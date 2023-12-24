<?php

namespace SwitcherCore\Modules\BDcom;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCountersNew extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getOids($interface_id = '')
    {
        return array_map(function ($e) use ($interface_id) {
            return Oid::init($e->getOid() . $interface_id);
        }, $this->interfaceCounterOids());
    }


    function getOidsByInterfacesArray($interfaces)
    {
        $oids = [];
        foreach ($interfaces as $iface) {
            $oids = array_merge($oids, $this->getOids('.' . $iface['xid']));
        }
        return $oids;
    }

    public function run($params = [])
    {
        $oidsLoc = $this->getOids();
        $suffix = '';
        $oids = [];

        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $suffix = '.' . $interface['xid'];
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
            return $this;
        }

        if ($params['interface_type'] == 'ONU') {
            $ifaces = array_filter($this->getInterfacesIds(), fn($v) => $v['type'] == 'ONU');
            $oids = $this->getOidsByInterfacesArray($ifaces);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } elseif ($params['interface_type'] == 'PHYSICAL') {
            $ifaces = $this->getPhysicalInterfaces();
            $oids = $this->getOidsByInterfacesArray($ifaces);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    if(!$iface['id']) continue;
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = $resp->getValue();
                } catch (\Exception $e) {

                }
            }
        }
        return array_values($data);
    }

    function getPretty()
    {
        return null;
    }

}