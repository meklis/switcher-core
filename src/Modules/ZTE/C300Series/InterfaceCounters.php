<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class InterfaceCounters extends ModuleAbstract
{

    function getOidsForPhysical()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, [
            $this->oids->getOidByName('if.InErrors'),
            $this->oids->getOidByName('if.OutErrors'),
            $this->oids->getOidByName('if.HCInOctets'),
            $this->oids->getOidByName('if.HCOutOctets'),
            $this->oids->getOidByName('if.HCInMulticastPkts'),
            $this->oids->getOidByName('if.HCOutMulticastPkts'),
            $this->oids->getOidByName('if.HCInBroadcastPkts'),
            $this->oids->getOidByName('if.HCOutBroadcastPkts'),
        ]);
    }

    function getOidsForOnts()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
    }

    public function run($params = [])
    {
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            if ($interface['type'] === 'ONU') {
                $oids = array_map(function ($e) use ($interface) {
                    return $e . "." . $interface['_xpon_id'];
                }, $this->getOidsForOnts());
            } else {
                $oids = array_map(function ($e) use ($interface) {
                    return $e . "." . $interface['_xid'];
                }, $this->getOidsForPhysical());
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
            return $this;
        }

        if ($params['interface_type'] === 'ONU') {
            $oids = $this->getOidsForOnts();
        } elseif ($params['interface_type'] === 'PHYSICAL') {
            $oids = $this->getOidsForPhysical();
        } else {
            $oids = array_merge($this->getOidsForOnts(), $this->getOidsForPhysical());
        }

        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }

    public function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["xpon.ont.counters.", "if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    if (strpos($oidName, "xpon.ont.counters") !== false) {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'xid');
                    }
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = $resp->getValue();
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
            }
        }
        return array_values($data);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}