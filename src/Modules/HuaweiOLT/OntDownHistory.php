<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntDownHistory extends HuaweiOLTAbstractModule
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
        $data = $this->getResponseByName('ont.regTable.uptime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid(), 1);
                $id = Helper::getIndexByOid($r->getOid());
                $time = null;
                if($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                    $time = $ts->getTimestamp();
                }
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['logs'][$id]['last_reg'] = $time;
                $ifaces[$iface['id']]['logs'][$id]['last_reg_since'] =  $time == null ? null :$this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.regTable.downTime');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid(), 1);
                $time = null;
                if($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                    $time = $ts->getTimestamp();
                }
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['logs'][$id]['last_dereg'] = $time;
                $ifaces[$iface['id']]['logs'][$id]['last_dereg_since'] = $time == null ? null : $this->getSince($time);
            }
        }
        $data = $this->getResponseByName('ont.regTable.downCause');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid(), 1);
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['logs'][$id]['last_down_reason'] = $r->getParsedValue();
            }
        }
        return array_values(array_map(function ($iface) {
            $iface['logs'] = array_values(array_filter(array_map(function ($e) {
                if(isset($e['last_down_reason']) && $e['last_down_reason'] == 'Invalid') return null;
                if(!isset($e['last_down_reason'])) $e['last_down_reason'] = null;
                if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
                if(!isset($e['last_dereg'])) $e['last_dereg'] = null;
                if(!isset($e['last_reg'])) $e['last_reg'] = null;
                if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
                return $e;
            }, $iface['logs']), function ($e) {
                return $e !== null;
            }));
            return $iface;
        }, $ifaces));
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $reasons[] = $this->oids->getOidByName('ont.regTable.uptime');
        $reasons[] = $this->oids->getOidByName('ont.regTable.downTime');
        $reasons[] = $this->oids->getOidByName('ont.regTable.downCause');

        $oids = [];
        foreach ($reasons as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e ."." .  $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
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

