<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
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
        $ifaces = [];
        foreach ($this->getInterfacesIds() as $v) {
            if(!$v['_llid_id']) continue;
            $ifaces[$v['_llid_id']] = [
                'interface' => $v,
                'last_reg' => null,
                'last_dereg' => null,
                'last_reg_since' => null,
                'last_dereg_since' => null,
                'last_down_reason' => null,
            ];
        }
        $data = $this->getResponseByName('ont.llidLastRegTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $llid = $this->getLLidFromOid($r->getOid());
                $time = $this->parseReasonTime($r->getHexValue());
                if(!isset($ifaces[$llid])) continue;
                $ifaces[$llid]['last_reg'] = $time;
                $ifaces[$llid]['last_reg_since'] =  $time == null ? null :$this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.llidLastDeregTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $llid = $this->getLLidFromOid($r->getOid());
                $time = $this->parseReasonTime($r->getHexValue());
                if(!isset($ifaces[$llid])) continue;
                $ifaces[$llid]['last_dereg'] = $time;
                $ifaces[$llid]['last_dereg_since'] = $time == null ? null : $this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.llidLastDeregTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if(!isset($ifaces[$llid])) continue;
                $ifaces[$llid]['last_down_reason'] = $r->getParsedValue();
            }
        }
        return $ifaces;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $reasons[] = $this->oids->getOidByName('ont.llidLastRegTime');
        $reasons[] = $this->oids->getOidByName('ont.llidLastDeregTime');
        $reasons[] = $this->oids->getOidByName('ont.llidLastDeregReason');

        $oids = [];
        foreach ($reasons as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->getInterfacesIds();
            $oids = array_map(function ($e) use ($iface) {
                return $e . $iface['_llid_id'];
            }, $oids);
        }
        $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }

    private function parseReasonTime($hex) {
        $elements = explode(":", $hex);
        $date =  hexdec($elements[0] . $elements[1]) . "-" .
            hexdec($elements[2]) . '-' .
            hexdec($elements[3]) . ' '  .
            hexdec($elements[4]) . ':'  .
            hexdec($elements[5]) . ':'  .
            hexdec($elements[6]);
        $time = \DateTime::createFromFormat("Y-m-d h:i:s", $date);
        if($time) {
            return $time->getTimestamp();
        } else {
            return null;
        }
    }
    private function getLLidFromOid($oid) {
        return "." .
        Helper::getIndexByOid($oid, 6) . "." .
        Helper::getIndexByOid($oid, 5) . "." .
        Helper::getIndexByOid($oid, 4) . "." .
        Helper::getIndexByOid($oid, 3) . "." .
        Helper::getIndexByOid($oid, 2) . "." .
        Helper::getIndexByOid($oid, 1) . "." .
        Helper::getIndexByOid($oid);
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

