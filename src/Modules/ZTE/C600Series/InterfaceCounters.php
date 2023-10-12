<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class InterfaceCounters extends ModuleAbstract
{
    public function run($params = [])
    {
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $oids = array_map(function ($e) use ($interface) {
                return Oid::init($e->getOid() . "." . $interface['_oid_id']);
            }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e)  {
                return Oid::init($e->getOid());
            }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    public function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName=>$dt) {
            if($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace("xpon.ont.counters.", "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']]['interface'] = $iface;
                $data[$iface['id']][$name] = $resp->getValue();
            }
        }
        return array_values($data);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}