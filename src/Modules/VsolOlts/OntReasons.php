<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReasons extends VsolOltsAbstractModule
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
        $data = $this->getResponseByName('ont.llidLastRegTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $llid = $this->getLLidFromOid($r->getOid());
                $time = $this->parseReasonTime($r->getHexValue());
                $ifaces[$llid]['interface'] = $this->parseInterface($llid);
                $ifaces[$llid]['last_reg'] = $time;
                $ifaces[$llid]['last_reg_since'] =  $time == null ? null :$this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.llidLastDeregTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $llid = $this->getLLidFromOid($r->getOid());
                $time = $this->parseReasonTime($r->getHexValue());
                $ifaces[$llid]['interface'] = $this->parseInterface($llid);
                $ifaces[$llid]['last_dereg'] = $time;
                $ifaces[$llid]['last_dereg_since'] = $time == null ? null : $this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.llidLastDeregReason');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $llid = $this->getLLidFromOid($r->getOid());
                $ifaces[$llid]['interface'] = $this->parseInterface($llid);
                $ifaces[$llid]['last_down_reason'] = $r->getParsedValue();
            }
        }
        return array_values(array_map(function ($e) {
            if(!isset($e['last_down_reason'])) $e['last_down_reason'] = null;
            if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
            if(!isset($e['last_dereg'])) $e['last_dereg'] = null;
            if(!isset($e['last_reg'])) $e['last_reg'] = null;
            if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
            return $e;
        }, $ifaces));
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
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . $iface['_llid_id'];
            }, $oids);
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
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

