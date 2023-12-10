<?php

namespace SwitcherCore\Modules\CData;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCounters extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

    public function getIndexByOidCdata($oid, $offset = 0) {
        $exploded = explode(".", $oid);
        foreach ($exploded as $item){
            if(strlen($item) > 5){
                return $item;
            }
        }
        return $exploded[count($exploded) - 1 - $offset];
    }

    public function run($params = [])
    {
        $oidsLoc = $this->interfaceCounterOids();
        $oids = [];
        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            if($interface['type'] == 'ONU'){ //one onu
                $oids = array_map(function ($e) use ($interface)  {
                    return Oid::init($e->getOid() .  "." . $interface['id']);
                }, $this->oids->getOidsByRegex('ont.counters'));
                $this->response = $this->formatResponse($this->snmp->walk($oids));
            }else{ //one port
                $oids = array_map(function ($e) use ($interface) {
                    return  Oid::init($e->getOid() . '.' . $interface['xid']);
                }, $oidsLoc);
                $this->response = $this->formatResponse($this->snmp->get($oids));
            }
            return $this;
        }
        if ($params['interface_type'] == 'ONU' || (!isset($params['interface']) && !isset($params['interface_type']))) { // all onu
            $oids = array_map(function ($e) {
                return Oid::init($e->getOid());
            }, $this->oids->getOidsByRegex('ont.counters'));
        }
        if ($params['interface_type'] != 'ONU' || (!isset($params['interface']) && !isset($params['interface_type']))) { // all ports
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid());
            }
        }
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }

    function getPrettyFiltered($params = [], $fromCache = false)
    {
        $ifaces = [];
        $onu_ifaces = [];
        $interface = ($params['interface']) ? $this->parseInterface($params['interface']) : false;

        if((isset($interface['type']) && $interface['type'] == 'ONU') || $params['interface_type'] == 'ONU' || (!isset($params['interface']) && !isset($params['interface_type']))){ // all or one onu
            foreach ($this->response as $oidName=>$dt) {
                if($dt->error()) {
                    continue;
                }
                $name = Helper::fromCamelCase(str_replace("ont.counters.", "", $oidName));
                foreach ($dt->fetchAll() as $resp) {
                    try {
                        $interface = $this->parseInterface($this->getIndexByOidCdata($resp->getOid()));
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

        if((isset($interface['type']) && $interface['type'] != 'ONU') || (isset($params['interface_type']) && $params['interface_type'] != 'ONU'  || (!isset($params['interface']) && !isset($params['interface_type'])))){ // all or one physical port
                $list = [];
                if($params['interface'] && isset($interface)){
                    $list[] = $interface;
                }else{
                    $list = $this->getInterfacesIds();
                }
                foreach ($list as $iface) {
                    $ifaces[$iface['xid']] = [
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