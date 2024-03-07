<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReasons extends HuaweiOLTAbstractModule
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
        try {
            $data = $this->getResponseByName('ont.gpon.lastUptime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $time = $this->parseReasonTime($r->getHexValue());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_reg'] = date("Y-m-d H:i:s", $time);
                    $ifaces[$iface['id']]['last_reg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.gpon.lastDownTime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $time = $this->parseReasonTime($r->getHexValue());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_dereg'] = $time ? date("Y-m-d H:i:s", $time) : null;
                    $ifaces[$iface['id']]['last_dereg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.gpon.lastDownCause');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_down_reason'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}
        try {
            $data = $this->getResponseByName('ont.epon.lastUptime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $time = $this->parseReasonTime($r->getHexValue());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_reg'] = date("Y-m-d H:i:s", $time);
                    $ifaces[$iface['id']]['last_reg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.epon.lastDownTime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $time = $this->parseReasonTime($r->getHexValue());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_dereg'] = $time ? date("Y-m-d H:i:s", $time) : null;
                    $ifaces[$iface['id']]['last_dereg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.epon.lastDownCause');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['last_down_reason'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}

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
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.lastUptime');
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.lastDownTime');
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.lastDownCause');

        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.lastUptime');
        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.lastDownTime');
        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.lastDownCause');

        $oids = [];
        foreach ($reasons as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $reasons = array_filter($reasons, function ($oid) use ($iface) {
                return strpos($oid->getName(), $iface['_technology']) !== false;
            });
            $oids = array_map(function ($e) use ($iface) {
                return  Oid::init($e->getOid() . "." . $iface['xid']);
            }, $reasons);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    private function parseReasonTime($hex) {
        $elements = explode(":", $hex);
        if(count($elements) < 7) return null;
        $date =  hexdec($elements[0] . $elements[1]) . "-" .
            str_pad(hexdec($elements[2]), 2, "0", STR_PAD_LEFT)  . '-' .
            str_pad(hexdec($elements[3]), 2, "0", STR_PAD_LEFT) . ' '  .
            str_pad(hexdec($elements[4]), 2, "0", STR_PAD_LEFT) . ':'  .
            str_pad(hexdec($elements[5]), 2, "0", STR_PAD_LEFT) . ':'  .
            str_pad(hexdec($elements[6]), 2, "0", STR_PAD_LEFT) ;

        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $date);
        if($time && $time->getTimestamp() > 86400) {
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

