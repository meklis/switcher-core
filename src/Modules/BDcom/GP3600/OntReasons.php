<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


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
        $data = $this->getResponseByName('ont.deactiveReason');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->parseInterface(Helper::getIndexByOid($r->getOid()));
                $ifaces[Helper::getIndexByOid($r->getOid())]['interface'] = $iface;
                $ifaces[Helper::getIndexByOid($r->getOid())]['last_down_reason'] = $r->getParsedValue();
            }
        }

        $data = $this->getResponseByName('if.LastChange');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                if(isset($ifaces[Helper::getIndexByOid($r->getOid())])) {
                    $ifaces[Helper::getIndexByOid($r->getOid())]['last_change_since'] =  null;
                    $ifaces[Helper::getIndexByOid($r->getOid())]['last_change'] = (new \DateTime())->setTimestamp(time()- $r->getValue())->format("Y-m-d h:i:s");
                }
            }
        }


        return array_values(array_map(function ($e) {
            if(!isset($e['last_down_reason'])) $e['last_down_reason'] = null;
            if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
            if(!isset($e['last_dereg'])) $e['last_dereg'] = null;
            if(!isset($e['last_reg'])) $e['last_reg'] = null;
            if(!isset($e['last_dereg_since'])) $e['last_dereg_since'] = null;
            if(!isset($e['last_change_time'])) $e['last_change_time'] = null;
            if(!isset($e['last_change_since'])) $e['last_change_since'] = null;
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
        $reasons[] = $this->oids->getOidByName('ont.deactiveReason');
        $reasons[] = $this->oids->getOidByName('if.LastChange');

        $oids = [];
        foreach ($reasons as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . '.' .  $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    private function getSince($time) {
        $timetrics =  $time;
        $days = floor($timetrics/ (24 * 60 * 60)   );
        $hours = floor(($timetrics - ((24 * 60 * 60)   * $days)) / (60 * 60) );
        $minutes = floor(($timetrics - ((24 * 60 * 60)  * $days) - ((60 * 60) * $hours) ) / 60 );
        $seconds = floor( ($timetrics - ((24 * 60 * 60)  * $days) - ((60 * 60) * $hours)- (60 * $minutes)) );
        return "{$days}d {$hours}h {$minutes}min {$seconds}sec";
    }
}

