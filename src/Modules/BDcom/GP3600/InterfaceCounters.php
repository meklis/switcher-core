<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * Class OntUniInformation
 * @package SwitcherCore\Modules\BDcom
 */
class InterfaceCounters extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }
    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $ifaces = [];
        if($filter['interface']) {
           $iface = $this->parseInterface($filter['interface']);
           $ifaces[$iface['xid']] = [
               'interface' => $iface,
               'in_errors' => null,
               'out_errors' => null,
               'in_octets' => null,
               'out_octets' => null,
               'in_multicast_pkts' => null,
               'out_multicast_pkts' => null,
               'in_broadcast_pkts' => null,
               'out_broadcast_pkts' => null,
           ];
        } else {
            foreach ($this->getInterfacesIds() as $iface) {
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
        return array_values($ifaces);
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
        $oidsLoc = [
            $this->oids->getOidByName('if.InErrors'),
            $this->oids->getOidByName('if.OutErrors'),
            $this->oids->getOidByName('if.InDiscards'),
            $this->oids->getOidByName('if.OutDiscards'),
            $this->oids->getOidByName('if.HCInOctets'),
            $this->oids->getOidByName('if.HCOutOctets'),
            $this->oids->getOidByName('if.HCInMulticastPkts'),
            $this->oids->getOidByName('if.HCOutMulticastPkts'),
            $this->oids->getOidByName('if.HCInBroadcastPkts'),
            $this->oids->getOidByName('if.HCOutBroadcastPkts'),
        ];
        $suffix = '';
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $suffix = '.'.$interface['xid'];
            $oids = [];
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = [];
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }
}

