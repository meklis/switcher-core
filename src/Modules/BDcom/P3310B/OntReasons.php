<?php


namespace SwitcherCore\Modules\BDcom\P3310B;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\BDcom\BDcomAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReasons extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }


    function getPretty()
    {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $data = [];
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            list($interfaceName, $onuNum) = explode(":", $iface['name']);
            $resp = $this->console->exec("sh epon inactive-onu interface {$interfaceName} {$onuNum}");
        } else {
            $resp = $this->console->exec("sh epon inactive-onu");
        }
        foreach (explode("\n", $resp) as $line) {
            if(preg_match('/^(EPON0\/[0-9]:[0-9]{1,2})\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)$/', $line, $m)) {
                $data[] = [
                    'interface' => $this->parseInterface($m[1]),
                    'last_down_reason' => $m[6],
                    'last_reg' => $this->parseDate($m[4]),
                    'last_dereg' => $this->parseDate($m[5]),
                    'last_reg_since' => $this->getSince($this->parseDate($m[4])),
                    'last_dereg_since' => $this->parseDate($m[5]),
                ];
            }
        }

        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            list($interfaceName, $onuNum) = explode(":", $iface['name']);
            $resp = $this->console->exec("sh epon active-onu interface {$interfaceName} {$onuNum}");
        } else {
            $resp = $this->console->exec("sh epon active-onu");
        }
        foreach (explode("\n", $resp) as $line) {
            if(preg_match('/^(EPON0\/[0-9]:[0-9]{1,2})\s*(\S*)\s*(\S*)\s*(\S*)\s*([0-9]{1,5})\s*([0-9]{1,5})\s*(\S*)\s*(\S*)\s*(\S*)\s*(\S*)$/', $line, $m)) {
                $data[] = [
                    'interface' => $this->parseInterface($m[1]),
                    'last_down_reason' => $m[9],
                    'last_reg' => $this->parseDate($m[7]),
                    'last_dereg' => $this->parseDate($m[8]),
                    'last_reg_since' => $this->getSince($this->parseDate($m[7])),
                    'last_dereg_since' => $this->parseDate($m[8]),
                ];
            }
        }
        $this->response = $data;
        return $this;
    }
    private function parseDate($date) {
        $date = \DateTime::createFromFormat("Y.m.d.H:i:s", $date);
        if($date) {
            return $date->getTimestamp();
        }
        return 0;
    }
    private function getSince($time) {
        $timetrics = time() - $time;
        $days = floor($timetrics/ (24 * 60 * 60)   );
        $hours = floor(($timetrics - ((24 * 60 * 60)   * $days)) / (60 * 60) );
        $minutes = floor(($timetrics - ((24 * 60 * 60)  * $days) - ((60 * 60) * $hours) ) / 60 );
        $seconds = floor( ($timetrics - ((24 * 60 * 60)  * $days) - ((60 * 60) * $hours)- (60 * $minutes)) );
        return "{$days}d {$hours}h {$minutes}min {$seconds}sec";
    }
}

