<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;



class InterfaceCounters extends ModuleAbstract
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

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
            $suffix = '.'.$interface['_xid_id'];
            $oids = [];
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } elseif ($filter['interface_type'] == 'ONU') { // all onu counters
            $oids = array_map(function ($e)  {
                return Oid::init($e->getOid());
            }, $this->oids->getOidsByRegex('^xpon.ont.counters'));
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        } else {
            $oids = [];
            foreach ($oidsLoc as $oid) {
                $oids[] = Oid::init($oid->getOid() . $suffix);
            }
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }
    function getPrettyFiltered($params = [], $fromCache = false)
    {
        if($params['interface_type'] == 'ONU'){
            $data = [];
            foreach ($this->response as $oidName=>$dt) {
                if($dt->error()) {
                    continue;
                }
                $name = Helper::fromCamelCase(str_replace("xpon.ont.counters.", "", $oidName));
                foreach ($dt->fetchAll() as $resp) {
                    try {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
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
        } else { // types TGE/GE/PON
            $ifaces = [];
            //$filter['interface'] = 0;
            if($params['interface']) {
                $iface = $this->parseInterface($params['interface']);
                $ifaces[$iface['_xid_id']] = [
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
                foreach ($this->listInterfacesByXidNames() as $iface) {
                    $iface_parsed = $this->parseInterface($iface['id']);
                    $ifaces[$iface_parsed['_xid_id']] = [
                        'interface' => $iface_parsed,
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

    }

    function getPretty()
    {
        return null;
    }
}