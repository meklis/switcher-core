<?php


namespace SwitcherCore\Modules\GCOM;


use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\BDcom\GP3600\BDcomAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTable extends GCOMAbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;


    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getPretty()
    {
        $response = [];
        $data = $this->getResponseByName('fdb.macAddress');
        if($data->error()) {
           throw new \SNMPException($data->error());
        }
        foreach ($data->fetchAll() as $d) {
            $iface = $this->parseInterface($this->getOnuXidByOid($d->getOid(), 1));
            $id = Helper::getIndexByOid($d->getOid());
            $response["{$iface['id']}.{$id}"] = [
                'interface' => $iface,
                'mac_address' => $d->getHexValue(),
            ];
        }

        $data = $this->getResponseByName('fdb.vlanId');
        if($data->error()) {
           throw new \SNMPException($data->error());
        }
        foreach ($data->fetchAll() as $d) {
            $iface = $this->parseInterface($this->getOnuXidByOid($d->getOid(), 1));
            $id = Helper::getIndexByOid($d->getOid());
            $response["{$iface['id']}.{$id}"]['vlan_id'] = (int)$d->getValue();
        }
        return array_values(array_filter($response, function ($mac) {
            if(!isset($mac['mac_address'])) return false;
            if(!isset($mac['vlan_id'])) return  false;
            if($mac['vlan_id'] > 4096) return  false;
            return true;
        }));
    }


    public function run($filter = [])
    {
       $oids = array_map(function ($o) {return $o->getOid();}, $this->oids->getOidsByRegex('^fdb\..*'));
       if($filter['interface']) {
           $iface = $this->parseInterface($filter['interface']);
           $oids = array_map(function ($o) use ($iface) {return $o . "." . $iface['xid']; }, $oids);
       } elseif ($filter['mac']) {
           throw new \Exception("Searching by mac-address not supported yet.");
       } elseif ($filter['vlan_id']) {
           throw new \Exception("Searching by vlan_id not supported yet.");
       }

       $oids = array_map(function ($o) { return \SnmpWrapper\Oid::init($o);}, $oids);
       $this->response = $this->formatResponse($this->snmp->walk($oids));
       return $this;
    }
}

