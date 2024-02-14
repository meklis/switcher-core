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
        $data = $this->getResponseByName('ont.lastDeregReason');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $snmpId = ".". Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                $ifaces[$snmpId]['last_down_reason'] = $r->getParsedValue();
            }
        }
        $data = $this->getResponseByName('ont.lastRegTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if($r->getValue() == 'N/A') continue;
                $snmpId = ".". Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $time = $this->parseTime($r->getValue());
                $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                $ifaces[$snmpId]['last_reg'] = date("Y-m-d H:i:s", $time);
                $ifaces[$snmpId]['last_reg_since'] = !$time ? null : $this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.lastDeregTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if($r->getValue() == 'N/A') continue;
                $snmpId = ".". Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $time = $this->parseTime($r->getValue());
                $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                $ifaces[$snmpId]['last_dereg'] = date("Y-m-d H:i:s", $time);
                $ifaces[$snmpId]['last_dereg_since'] = !$time ? null : $this->getSince($time);
            }
        }

        $data = $this->getResponseByName('ont.aliveTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if($r->getValue() == 'N/A') continue;
                $snmpId = ".". Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                $ifaces[$snmpId]['interface'] = $this->parseInterface($snmpId);
                $ifaces[$snmpId]['alive_time'] = $r->getValue();
            }
        }
        return array_values(array_map(function ($e) {
            if(!isset($e['last_down_reason'])) $e['last_down_reason'] = null;
            if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
            if(!isset($e['last_dereg'])) $e['last_dereg'] = null;
            if(!isset($e['last_reg'])) $e['last_reg'] = null;
            if(!isset($e['last_reg_since'])) $e['last_reg_since'] = null;
            if(!isset($e['alive_time'])) $e['alive_time'] = null;
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
        $reasons[] = $this->oids->getOidByName('ont.lastDeregReason');
        $reasons[] = $this->oids->getOidByName('ont.lastDeregTime');
        $reasons[] = $this->oids->getOidByName('ont.lastRegTime');
        $reasons[] = $this->oids->getOidByName('ont.aliveTime');

        $oids = [];
        foreach ($reasons as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {return Oid::init($e . $iface['_snmp_id']); }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    private function parseTime($date) {
        $time = \DateTime::createFromFormat("Y/m/d H:i:s", trim($date));
        if($time) {
            return $time->getTimestamp();
        } else {
            return null;
        }
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

