<?php


namespace SwitcherCore\Dev\Mock;


use SnmpWrapper\NoProxy\SnmpInterface;

class PhpSnmp implements SnmpInterface
{
    protected $types = [
        2 => 'Integer',
        4 => 'String',
        6 => 'OID',
        65 => 'Integer',
        66 => 'Integer',
        67 => 'Timeticks',
        70 => 'Counters64',
        64 => 'IpAddress',
    ];
    const SET_TYPE_INTEGER = 'i';
    const SET_TYPE_STRING = 's';

    protected $ip;
    protected $community;
    protected $timeoutMs;
    protected $retries;

    protected $snmpVersion = \SNMP::VERSION_2C;

    protected $port = 161;

    protected $walkNextSleep = 0;

    public function getWalkNextSleep(): int
    {
        return $this->walkNextSleep;
    }

    public function setWalkNextSleep(int $walkNextSleep): PhpSnmp
    {
        $this->walkNextSleep = $walkNextSleep;
        return $this;
    }

    protected $mockData = [];
    public function setMockFile($path)
    {
        if(!file_exists($path)) {
            throw new \Exception("File $path not exists");
        }
        $lines = explode("\n", file_get_contents($path));
        foreach ($lines as $line) {
            list($oid, $data) = explode(" = ", $line);
            $index = trim($oid, ". ");
            $type = 4;
            /**
             * 2 => 'Integer',
             * 4 => 'String',
             * 6 => 'OID',
             * 65 => 'Integer',
             * 66 => 'Integer',
             * 67 => 'Timeticks',
             * 70 => 'Counters64',
             * 64 => 'IpAddress',
             */
            if(preg_match('/^(.*?): (.*)$/', $data, $m)) {
                switch ($m[1]) {
                    case 'INTEGER': $type = 2; break;
                    case 'Gauge32': $type = 2; break;
                    case 'Gauge64': $type = 2; break;
                    case 'STRING': $type = 4; break;
                    case 'Hex-STRING': $type = 4; break;
                    case 'OID': $type = 6; break;
                    case 'Timeticks': $type = 67; break;
                    case 'Counter32': $type = 2; break;
                    case 'Counter64': $type = 70; break;
                    case 'IpAddress': $type = 64; break;
                }
                $data = $m[2];
            }
            $this->mockData[$index] = [
                'oid' => $oid,
                'type' => $type,
                'value' => trim($data, '"'),
            ];
        }
        return $this;
    }


    /**
     * PhpSnmp constructor.
     * @param string $ip
     * @param string $community
     * @param int|null $timeout
     */
    public function __construct(string $ip, string $community, int $timeout_ms = null, $retries = null, $port = null)
    {
        if (!$timeout_ms) {
            $timeout_ms = -1;
        } else {
            $timeout_ms *= 1000;
        }
        if (!$retries) {
            $retries = -1;
        }
        if ($port) {
            $this->port = $port;
        }
        $this->ip = $ip;
        $this->community = $community;
        $this->timeoutMs = $timeout_ms;
        $this->retries = $retries;
    }

    /**
     * @return int
     */
    public function getSnmpVersion(): int
    {
        return $this->snmpVersion;
    }

    /**
     * @param string $snmpVersion
     */
    public function setSnmpVersion(string $snmpVersion)
    {
        switch ($snmpVersion) {
            case '1'; $this->snmpVersion = \SNMP::VERSION_1; break;
            case '2'; $this->snmpVersion = \SNMP::VERSION_2c; break;
            case '2c'; $this->snmpVersion = \SNMP::VERSION_2c; break;
            case '3':  $this->snmpVersion = \SNMP::VERSION_3; break;
        }
        return $this;
    }


    /**
     * @param string $oid
     * @return array
     * @throws \Exception
     */
    function walkNext(string $oid, $checkNext = true)
    {
        $response = [];
        $firstOid = $oid;
        $snmp = $this->getSnmp();
        while ($objs = $snmp->getnext([$oid])) {
            foreach ($objs as $oid => $obj) {
                if ($checkNext && strpos($oid, $firstOid . ".") === false) {
                    break(2);
                }
                if ($obj->type == 67) {
                    $obj->value = $this->parseTimeTicks($obj->value);
                }
                if (in_array($obj->type, [2, 65, 66, 70])) {
                    $obj->value = (int)filter_var($obj->value, FILTER_SANITIZE_NUMBER_INT);
                }
                $response[] = [
                    'oid' => $oid,
                    'type' => $this->types[$obj->type],
                    'value' => trim($obj->value, '"'),
                ];
            }
            if($this->walkNextSleep != 0) {
                usleep($this->walkNextSleep);
            }
        }
        return $response;
    }

    /**
     * @param string $oid
     * @return array
     * @throws \Exception
     */
    function walk(string $oid)
    {
        $snmp = $this->getSnmp();
        $response = [];
        $objs = @$snmp->walk($oid);
        if (!$objs) {
            throw new \Exception($snmp->getError(), $snmp->getErrno());
        }
        foreach ($objs as $oid => $obj) {
            if ($obj->type == 67) {
                $obj->value = $this->parseTimeTicks($obj->value);
            }
            if (in_array($obj->type, [2, 65, 66, 70])) {
                $obj->value = (int)filter_var($obj->value, FILTER_SANITIZE_NUMBER_INT);
            }
            $response[] = [
                'oid' => $oid,
                'type' => $this->types[$obj->type],
                'value' => trim($obj->value, '"'),
            ];
        }
        $snmp->close();
        return $response;
    }

    private function parseTimeTicks($timetick)
    {

        $data = explode(":", $timetick);
        return
            //Дни
            (($data[0] * 24 * 60 * 60) +
                //Часы
                ($data[1] * 60 * 60) +
                //Минуты
                ($data[2] * 60) +
                //Секунды
                ((int)$data[3]));
    }

    /**
     * @param array $oids
     * @return array
     */
    function multiWalk(array $oids)
    {
        $resp = [];
        foreach ($oids as $oid) {
            $response = null;
            $err = null;
            try {
                $response = $this->walk($oid);
            } catch (\Exception $e) {
                $err = $e;
            }
            $resp[] = [
                'oid' => $oid,
                'response' => $response,
                'error' => $err,
            ];
        }
        return $resp;
    }

    /**
     * @param array $oids
     * @return array
     */
    function multiWalkNext(array $oids)
    {
        $resp = [];
        foreach ($oids as $oid) {
            $response = null;
            $err = null;
            try {
                $response = $this->walkNext($oid);
            } catch (\Exception $e) {
                $err = $e;
            }
            $resp[] = [
                'oid' => $oid,
                'response' => $response,
                'error' => $err,
            ];
        }
        return $resp;
    }


    /**
     * @param string $oid
     * @return array
     * @throws \Exception
     */
    function get(string $oid)
    {
        $snmp = $this->getSnmp();
        $obj = @$snmp->get($oid);
        if (!$obj) {
            throw new \Exception($snmp->getError(), $snmp->getErrno());
        }

        if ($obj->type == 67) {
            $obj->value = $this->parseTimeTicks($obj->value);
        }
        if (in_array($obj->type, [2, 65, 66, 70])) {
            $obj->value = (int)filter_var($obj->value, FILTER_SANITIZE_NUMBER_INT);
        }
        $snmp->close();
        return [
            'oid' => $oid,
            'type' => $this->types[$obj->type],
            'value' => trim($obj->value, '"'),
        ];
    }

    /**
     * @param array $oids
     * @return array
     * @throws \Exception
     */
    function multiGet(array $oids)
    {
        $snmp = $this->getSnmp();
        $resp = [];
        foreach ($oids as $oid) {
            $response = null;
            $err = null;
            try {
                $obj = $this->get($oid);
                $resp[] = [
                    '_oid' => $obj['oid'],
                    'oid' => $obj['oid'],
                    'type' => $obj['type'],
                    'value' => $obj['value'],
                    'error' => $err,
                ];
            } catch (\Exception $e) {
                $err = $e;
                $resp[] = [
                    '_oid' => $oid,
                    'oid' => null,
                    'type' => null,
                    'value' => null,
                    'error' => $err,
                ];
            }
        }
        $snmp->close();
        return $resp;
    }

    /**
     * @param string $oid
     * @param string $type
     * @param $value
     * @return $this
     * @throws \Exception
     */
    function set(string $oid, string $type, $value)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        $snmp = $this->getSnmp();
        $snmp->set($oid, $type, $value);
        $snmp->close();
        restore_error_handler();
        return $this;
    }

}
