<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCountersFD16 extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getOidsForOnts()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('ont.counters'));
    }

    function getOidsForPhysical()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->getInterfaceCountersOids());
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
            $interface = $this->parseInterface($params['interface']);
            if ($interface['type'] == 'ONU') {
                $this->response = [];
                return $this;
            } else {
                $oids = $this->getInterfaceOids($interface['_snmp_id'], $this->getOidsForPhysical());
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } elseif ($params['interface_type'] == 'ONU' ) {
            $this->response = [];
            return $this;
        } else {
            $physOids = $this->getOidsForPhysical();
            $oids = [];
            foreach ($this->getPhysicalInterfaces() as $iface) {
                $oids = array_merge($oids, $this->getInterfaceOids( $iface['_snmp_id'], $physOids));
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        }
        return $this;
    }

    function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["ont.counters.", "if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    if (strpos($oidName, "ont.counters") !== false) {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 2));
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'xid');
                    }
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = $resp->getValue();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
            }
        }
        return array_values($data);
    }

    function getPrettyFiltered($params = [], $fromCache = false)
    {
        return $this->getPretty();
    }
}