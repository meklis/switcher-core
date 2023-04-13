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
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }
    function getPrettyFiltered($filter = [], $fromCache = false)
    {

        $data = $this->getResponseByName('ont.stat.inBytes');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['in_octets'] = (float)$r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.stat.outBytes');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['out_octets'] = (float)$r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.stat.inDropPkts');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['in_drop_pkts'] = (float)$r->getValue();
            }
        }
        $data = $this->getResponseByName('ont.stat.outDropPkts');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $iface = $this->findIfaceByOid($r->getOid());
                $ifaces[$iface['id']]['interface'] = $iface;
                $ifaces[$iface['id']]['out_drop_pkts'] = (float)$r->getValue();
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
            $this->oids->getOidByName('ont.stat.outBytes'),
            $this->oids->getOidByName('ont.stat.inBytes'),
            $this->oids->getOidByName('ont.stat.outDropPkts'),
            $this->oids->getOidByName('ont.stat.inDropPkts'),
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

