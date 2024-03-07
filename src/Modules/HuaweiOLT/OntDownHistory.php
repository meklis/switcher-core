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
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $ifaces = [];

        try {
            $data = $this->getResponseByName('ont.gpon.regTable.uptime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $id = Helper::getIndexByOid($r->getOid());
                    $time = null;
                    if ($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif ($ts = \DateTime::createFromFormat("d.m.Y H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\+.*$/', $r->getValue(), $m) &&
                        $ts = \DateTime::createFromFormat("Y-m-d H:i:s", $m[1])
                    ) {
                        $time = $ts->getTimestamp();
                    }
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['reg_time'] = $time ? date("Y-m-d H:i:s", $time) : null;;
                    $ifaces[$iface['id']]['logs'][$id]['reg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.gpon.regTable.downTime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $time = null;
                    if ($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif ($ts = \DateTime::createFromFormat("d.m.Y H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\+.*$/', $r->getValue(), $m) &&
                        $ts = \DateTime::createFromFormat("Y-m-d H:i:s", $m[1])
                    ) {
                        $time = $ts->getTimestamp();
                    }
                    $id = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['dereg_time'] = $time ? date("Y-m-d H:i:s", $time) : null;
                    $ifaces[$iface['id']]['logs'][$id]['dereg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.gpon.regTable.downCause');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $id = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['down_reason'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}


        try {
            $data = $this->getResponseByName('ont.epon.regTable.uptime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $id = Helper::getIndexByOid($r->getOid());
                    $time = null;
                    if ($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif ($ts = \DateTime::createFromFormat("d.m.Y H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\+.*$/', $r->getValue(), $m) &&
                        $ts = \DateTime::createFromFormat("Y-m-d H:i:s", $m[1])
                    ) {
                        $time = $ts->getTimestamp();
                    }
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['reg_time'] = $time ? date("Y-m-d H:i:s", $time) : null;;
                    $ifaces[$iface['id']]['logs'][$id]['reg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.epon.regTable.downTime');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $time = null;
                    if ($ts = \DateTime::createFromFormat("Y-m-d H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif ($ts = \DateTime::createFromFormat("d.m.Y H:i:s", trim($r->getValue(), "Z"))) {
                        $time = $ts->getTimestamp();
                    } elseif (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})\+.*$/', $r->getValue(), $m) &&
                        $ts = \DateTime::createFromFormat("Y-m-d H:i:s", $m[1])
                    ) {
                        $time = $ts->getTimestamp();
                    }
                    $id = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['dereg_time'] = $time ? date("Y-m-d H:i:s", $time) : null;
                    $ifaces[$iface['id']]['logs'][$id]['dereg_since'] = $time == null ? null : $this->getSince($time);
                }
            }
            $data = $this->getResponseByName('ont.epon.regTable.downCause');
            if (!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $iface = $this->findIfaceByOid($r->getOid(), 1);
                    $id = Helper::getIndexByOid($r->getOid());
                    $ifaces[$iface['id']]['interface'] = $iface;
                    $ifaces[$iface['id']]['logs'][$id]['down_reason'] = $r->getParsedValue();
                }
            }
        } catch (\Exception $e) {}


        return array_values(array_map(function ($iface) {
            $iface['logs'] = array_values(array_filter(array_map(function ($e) {
                if (isset($e['down_reason']) && $e['down_reason'] == 'Invalid') return null;
                if (!isset($e['down_reason'])) $e['down_reason'] = null;
                if (!isset($e['dereg_time'])) $e['dereg_time'] = null;
                if (!isset($e['reg_time'])) $e['reg_time'] = null;
                if (!isset($e['dereg_since'])) $e['dereg_since'] = null;
                if (!isset($e['reg_since'])) $e['reg_since'] = null;
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
        $reasons = [];
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.regTable.uptime');
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.regTable.downTime');
        if($this->isHasGponIfaces()) $reasons[] = $this->oids->getOidByName('ont.gpon.regTable.downCause');

        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.regTable.uptime');
        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.regTable.downTime');
        if($this->isHasEponIfaces()) $reasons[] = $this->oids->getOidByName('ont.epon.regTable.downCause');

        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $reasons = array_filter($reasons, function ($oid) use ($iface) {
               return strpos($oid->getName(), $iface['_technology']) !== false;
            });
            $oids = array_map(function ($e) use ($iface) {
                return  Oid::init($e->getOid() . "." . $iface['xid']);
            }, $reasons);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        } else {
            $oids = array_map(function ($e) {
                return Oid::init($e->getOid());
            }, $reasons);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    private function getSince($time)
    {
        $timetrics = time() - $time;
        $days = floor($timetrics / (24 * 60 * 60));
        $hours = floor(($timetrics - ((24 * 60 * 60) * $days)) / (60 * 60));
        $minutes = floor(($timetrics - ((24 * 60 * 60) * $days) - ((60 * 60) * $hours)) / 60);
        $seconds = floor(($timetrics - ((24 * 60 * 60) * $days) - ((60 * 60) * $hours) - (60 * $minutes)));
        return "{$days}d {$hours}h {$minutes}min {$seconds}sec";
    }
}

