<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCounters extends ModuleAbstract
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    public function run($filter = [])
    {
        $oidsLoc = $this->interfaceCounterOids();
        $suffix = '';
        $oids = [];
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if($interface['type'] == 'ONU'){ // one onu
                $oids = array_map(function ($e) use ($interface) {
                    return Oid::init($e->getOid() . "." . $interface['_xpon_id']);
                }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
            }else{ // one port
                $suffix = '.'.$interface['_xid'];
                $oids = [];
                foreach ($oidsLoc as $oid) {
                    $oids[] = Oid::init($oid->getOid() . $suffix);
                }
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
            return $this;
        }
        if ($filter['interface_type'] == 'ONU' || (!$filter['interface'] && !$filter['interface_type'])) { //  all onu || without arguments
            $oids = array_map(function ($e)  {
                return Oid::init($e->getOid());
            }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
        }
        if ($filter['interface_type'] == 'PHYSICAL' || (!$filter['interface'] && !$filter['interface_type'])) { //  all ports || without arguments
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
        }

        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
    function getPrettyFiltered($params = [], $fromCache = false)
    {
        $ifaces = [];
        $onu_ifaces = [];

        if($params['interface']){
            $interface = $this->parseInterface($params['interface']);
        }

        if((isset($interface['type']) && $interface['type'] == 'ONU') || $params['interface_type'] == 'ONU' || (!isset($params['interface']) && !isset($params['interface_type']))){ // one onu || all onu || without arguments
            foreach ($this->response as $oidName=>$dt) {
                if($dt->error()) {
                    continue;
                }
                $name = Helper::fromCamelCase(str_replace("xpon.ont.counters.", "", $oidName));
                foreach ($dt->fetchAll() as $resp) {
                    try {
                        $interface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                        if($interface['type'] != 'ONU'){
                            continue;
                        }
                        $onu_ifaces[$interface['id']]['interface'] = $interface;
                        $onu_ifaces[$interface['id']][$name] = $resp->getValue();
                    } catch (\Exception $e) {
                        if (strpos($e->getMessage(), "not in service card") === false) {
                            throw $e;
                        }
                    }
                }
            }
        }

        if((isset($interface['type']) && $interface['type'] != 'ONU') || ($params['interface_type'] && $params['interface_type'] == 'PHYSICAL') || (!$params['interface'] && !$params['interface_type'])){ // one port || all ports || without arguments
            $list = [];
            if($params['interface'] && isset($interface)){
                $list[] = $interface;
            }else{
                $list = $this->listInterfacesByXidNames();
            }
                foreach ($list as $iface) {
                    $ifaces[$iface['_xid']] = [
                       'interface' => $iface,
                        'in_errors' => null,
                        'out_errors' => null,
                        'in_discards' => null,
                        'out_discards' => null,
                        'in_octets' => null,
                        'out_octets' => null,
                        'in_multicast_pkts' => null,
                        'out_multicast_pkts' => null,
                        'in_broadcast_pkts' => null,
                        'out_broadcast_pkts' => null,
                    ];
                }

            $data = $this->getResponseByName('if.InErrors');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['in_errors'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.OutErrors');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['out_errors'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.InDiscards');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['in_discards'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.OutDiscards');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['out_discards'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCInOctets');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['in_octets'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCOutOctets');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['out_octets'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCInMulticastPkts');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['in_multicast_pkts'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCOutMulticastPkts');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['out_multicast_pkts'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCInBroadcastPkts');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['in_broadcast_pkts'] = (float)$r->getValue();
                }
            }
            $data = $this->getResponseByName('if.HCOutBroadcastPkts');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    if(!isset($ifaces[$xid])) continue;
                    $ifaces[$xid]['out_broadcast_pkts'] = (float)$r->getValue();
                }
            }
        }

        return array_values(array_merge($onu_ifaces, $ifaces));
    }

    function getPretty()
    {
        return $this->getPrettyFiltered();
    }

}