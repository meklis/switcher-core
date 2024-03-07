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
            $name = Helper::fromCamelCase(str_replace(["ont.gpon.stat.","ont.epon.stat.", "if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    if (strpos($oidName, "ont.gpon") !== false) {
                        $iface = $this->findIfaceByOid($resp->getOid());
                    } elseif (strpos($oidName, "ont.epon") !== false) {
                        $iface = $this->findIfaceByOid($resp->getOid(), 1);
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    }
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = (float)$resp->getValue();
                } catch (\Exception $e) {

                }
            }
        }
        return array_values(array_map(function ($e) {
            if(!isset($e['in_octets']) || !isset($e['out_octets'])) {
                $e['in_octets'] = 0;
                $e['out_octets'] = 0;
            }
            if($e['in_octets'] > 1000000 && $e['out_octets'] > 1000000 && $e['in_octets'] == $e['out_octets']) {
                $e['in_octets'] = 0;
                $e['out_octets'] = 0;
            }
            return $e;
        },  $data));

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
                $oids = $this->getOnuOids($interface['xid'], $interface['_technology']);
                $this->response = $this->formatResponse($this->snmp->get($oids));
            } else {
                $oids = $this->getPhysicalIfacesOids($interface['xid']);
                $this->response = $this->formatResponse($this->snmp->get($oids));
            }
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

    function getOnuOids($suffix = '', $technology = null)
    {
        $epon = true;
        $gpon = true;
        if($technology) {
            if($technology == 'epon') $gpon = false;
            if($technology == 'gpon') $epon = false;
        }

        if ($suffix) {
            $suffix = ".{$suffix}" . ($epon ? ".0" : '');
        } else {
            $epon = false;
        }

        $oids = [];
        if($this->isHasEponIfaces() && $epon) $oids[] = Oid::init($this->oids->getOidByName('ont.epon.stat.outOctets')->getOid() . "{$suffix}");
        if($this->isHasEponIfaces() && $epon) $oids[] = Oid::init($this->oids->getOidByName('ont.epon.stat.inOctets')->getOid() . "{$suffix}");
        if($this->isHasGponIfaces() && $gpon) $oids[] = Oid::init($this->oids->getOidByName('ont.gpon.stat.outOctets')->getOid() . "{$suffix}");
        if($this->isHasGponIfaces() && $gpon) $oids[] = Oid::init($this->oids->getOidByName('ont.gpon.stat.inOctets')->getOid() . "{$suffix}");
        if($this->isHasGponIfaces() && $gpon) $oids[] = Oid::init($this->oids->getOidByName('ont.gpon.stat.outDropPkts')->getOid() . "{$suffix}");
        if($this->isHasGponIfaces() && $gpon) $oids[] = Oid::init($this->oids->getOidByName('ont.gpon.stat.inDropPkts')->getOid() . "{$suffix}");

        return $oids;
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

