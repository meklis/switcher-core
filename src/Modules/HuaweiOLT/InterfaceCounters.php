<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * Class OntUniInformation
 * @package SwitcherCore\Modules\HuaweiOLT
 */
class InterfaceCounters extends HuaweiOLTAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {

        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["ont.stat.", "if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    if (strpos($oidName, "ont.stat.") !== false) {
                        $iface = $this->findIfaceByOid($resp->getOid());
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    }
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = (float)$resp->getValue();
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

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] == 'ONU') {
                $oids = $this->getOnuOids($interface['xid']);
            } else {
                $oids = $this->getPhysicalIfacesOids($interface['xid']);
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
            return $this;
        }

        if (isset($filter['interface_type']) && $filter['interface_type'] === 'ONU') {
            $oids = $this->getOnuOids();
        } elseif (isset($filter['interface_type']) && $filter['interface_type'] === 'PHYSICAL') {
            $oids = $this->getPhysicalIfacesOids();
        } else {
            $oids = array_merge($this->getOnuOids(), $this->getPhysicalIfacesOids());
        }
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }

    function getOnuOids($suffix = '')
    {
        if ($suffix) {
            $suffix = ".{$suffix}";
        }
        return [
            Oid::init($this->oids->getOidByName('ont.stat.outOctets')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('ont.stat.inOctets')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('ont.stat.outDropPkts')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('ont.stat.inDropPkts')->getOid() . "{$suffix}"),
        ];
    }

    function getPhysicalIfacesOids($suffix = '')
    {
        if ($suffix) {
            $suffix = ".{$suffix}";
        }
        return [
            Oid::init($this->oids->getOidByName('if.InErrors')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('if.OutErrors')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('if.InDiscards')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('if.OutDiscards')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('if.HCInOctets')->getOid() . "{$suffix}"),
            Oid::init($this->oids->getOidByName('if.HCOutOctets')->getOid() . "{$suffix}"),
        ];
    }
}

