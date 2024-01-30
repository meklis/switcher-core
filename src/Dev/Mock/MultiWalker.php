<?php


namespace SwitcherCore\Dev\Mock;


use SnmpWrapper\Device;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Dev\Mock\PhpSnmp;

class MultiWalker implements MultiWalkerInterface
{
    /**
     * @var Device[]
     */
    protected $devices = [];


    function addDevice(Device $device)
    {
        $this->devices[] = $device;
        return $this;
    }


    function addDevices(array $devices)
    {
        $this->devices = array_merge($this->devices, $devices);
        return $this;
    }

    function flushDevices()
    {
        $this->devices = [];
    }

    /**
     * @param Oid[] $oids
     * @return string[]
     */
    private function getOidFromObjs(array $oids) {
        $ois = [];
        foreach ($oids as $oid) {
            $ois[] = $oid->getOid();
        }
        return $ois;
    }
    /**
     * @param Oid[] $oids
     * @return PoollerResponse[]
     */
    function walk(array $oids, $timeoutSec = null, $repeats = null)
    {
        $response = [];
        foreach ($this->devices as $device) {
            $timeout = $timeoutSec !== null ? $timeoutSec : $device->getTimeout();
            $countRepeats = $repeats !== null ? $repeats : $device->getRepeats();
            $oidResponses = (new PhpSnmp($device->getIp(), $device->getPubCommunity(), $timeout * 1000, $countRepeats, $device->getPort()))->setSnmpVersion($device->getVersion())->multiWalk($this->getOidFromObjs($oids));
            foreach ($oidResponses as $data) {
                $pooller = PoollerResponse::init($device->getIp(), $data['oid'], null, $data['error']);
                if (!$data['error']) {
                    $snmpResponses = [];
                    foreach ($data['response'] as $resp) {
                        $snmpResponses[] = SnmpResponse::init($resp['oid'], $resp['type'], $resp['value'], $this->wrapStrToHex($resp['value']));
                    }
                    $pooller->setResponse($snmpResponses);
                }
                $response[] = $pooller;
            }
        }
        return $response;
    }


    function isHex($str) {
        $str = str_replace(["\'","\"","\n", " ", "-", ":", "\t"], '', $str);
        return preg_match('/^[[:xdigit:]]{1,}$/', $str);
    }

    /**
     * @param $val
     * @return string
     */
    private function wrapStrToHex($val)
    {
        if(is_numeric($val)) {
            $hx = strtoupper(bin2hex($val));
        } elseif ($this->isHex($val)) {
            $hx = str_replace(["\'","\"","\n", " ", "-", ":", "\t"], '', $val);
        } else {
            if (is_numeric($val)) {
                $val = strtoupper(dechex($val));
            }
            $hx = strtoupper(bin2hex($val));
        }
        $result = '';
        $lims = 0;
        foreach (str_split ($hx) as $h) {
            $lims++;
            $result .= $h;
            if($lims >= 2) {
                $result .= ':';
                $lims = 0;
            }
        }
        return trim($result, ':');
    }

    function walkBulk(array $oids, $timeoutSec = null, $repeats = null)
    {
        return $this->walk($oids, $timeoutSec, $repeats);
    }

    function get(array $oids, $timeoutSec = null, $repeats = null)
    {
        $response = [];
        foreach ($this->devices as $device) {
            $timeout = $timeoutSec !== null ? $timeoutSec : $device->getTimeout();
            $countRepeats = $repeats !== null ? $repeats : $device->getRepeats();
            $oidResponses = (new PhpSnmp($device->getIp(), $device->getPubCommunity(), $timeout * 1000, $countRepeats, $device->getPort()))->setSnmpVersion($device->getVersion())->multiGet($this->getOidFromObjs($oids));

            foreach ($oidResponses as $data) {
                if(!$data['oid']) $data['oid'] = $data['_oid'];
                $pooller = PoollerResponse::init($device->getIp(), $data['oid'], null, $data['error']);
                if (!$data['error']) {
                    $pooller->setResponse([SnmpResponse::init($data['oid'], $data['type'], $data['value'], $this->wrapStrToHex($data['value']))]);
                }
                $response[] = $pooller;
            }
        }
        return $response;
    }

    function set(Oid $oid, $timeoutSec = null, $repeats = null)
    {
        $response = [];
        foreach ($this->devices as $device) {
            try {
                $timeout = $timeoutSec !== null ? $timeoutSec : $device->getTimeout();
                $countRepeats = $repeats !== null ? $repeats : $device->getRepeats();
                $type = null;
                switch($oid->getType()) {
                    case 'Integer': $type = PhpSnmp::SET_TYPE_INTEGER; break;
                    default: $type = PhpSnmp::SET_TYPE_STRING;
                }
                (new PhpSnmp($device->getIp(), $device->getPrivateCommunity(), $timeout * 1000, $countRepeats, $device->getPort()))
                    ->setSnmpVersion($device->getVersion())
                    ->set($oid->getOid(), $type, $oid->getValue());
                $response[] = PoollerResponse::init($device->getIp(), $oid->getOid(), [SnmpResponse::init(
                    $oid->getOid(),
                    $oid->getType(),
                    $oid->getValue(),
                    $this->wrapStrToHex($oid->getValue())
                )]);
            } catch (\Exception $e) {
                $response[] = PoollerResponse::init($device->getIp(), $oid->getOid(), null, $e->getMessage());
            }
        }
        return $response;
    }

    function walkNext(array $oids, $timeoutSec = null, $repeats = null, $walkNextSleep = 0)
    {
        $response = [];
        foreach ($this->devices as $device) {
            $timeout = $timeoutSec !== null ? $timeoutSec : $device->getTimeout();
            $countRepeats = $repeats !== null ? $repeats : $device->getRepeats();
            $oidResponses = (new PhpSnmp($device->getIp(), $device->getPubCommunity(), $timeout * 1000, $countRepeats, $device->getPort()))->setWalkNextSleep($walkNextSleep)->setSnmpVersion($device->getVersion())->multiWalkNext($this->getOidFromObjs($oids));
            foreach ($oidResponses as $data) {
                $pooller = PoollerResponse::init($device->getIp(), $data['oid'], null, $data['error']);
                if (!$data['error']) {
                    $snmpResponses = [];
                    foreach ($data['response'] as $resp) {
                        $snmpResponses[] = SnmpResponse::init($resp['oid'], $resp['type'], $resp['value'], $this->wrapStrToHex($resp['value']));
                    }
                    $pooller->setResponse($snmpResponses);
                }
                $response[] = $pooller;
            }
        }
        return $response;
    }


}