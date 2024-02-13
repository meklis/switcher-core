<?php

namespace SwitcherCore\Modules\CData\FD11XX;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCounters extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getOidsForPhysical()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->interfaceCounterOids());
    }

    function getInterfaceOids($interface_id, $oids)
    {
        return array_map(function ($e) use ($interface_id) {
            return Oid::init($e->getOid() . '.' . $interface_id);
        }, $oids);
    }

    public function run($params = [])
    {
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface'], 'id');
            if ($interface['type'] == 'ONU') {
                //throw new \InvalidArgumentException("ONU oids not found at the moment, just physical ports");
                return $this;
            } else {
                $oids = $this->getInterfaceOids($interface['phys_snmp_id'], $this->getOidsForPhysical());
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } elseif ($params['interface_type'] == 'ONU') {
            //throw new \InvalidArgumentException("ONU oids not found at the moment, just physical ports");
            return $this;
        } else {
            $physOids = $this->getOidsForPhysical();
            $oids = [];
            foreach ($this->getInterfacesIds() as $iface) {
                $oids = array_merge($oids, $this->getInterfaceOids($iface['phys_snmp_id'], $physOids));
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        }
        return $this;
    }

    function getPretty()
    {
        $data = [];
        if (empty($this->response)) return [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'id');
                $data[$iface['id']]['interface'] = $iface;
                $data[$iface['id']][$name] = $resp->getValue();
            }
        }
        return array_values($data);
    }

    function getPrettyFiltered($params = [], $fromCache = false)
    {
        return $this->getPretty();
    }
}