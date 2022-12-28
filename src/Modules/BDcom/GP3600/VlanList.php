<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class VlanList extends BDcomAbstractModule
{


    protected $interfaces;


    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        return $data;
    }

    function getPretty()
    {
        $data = $this->getResponseByName('dot1q.VlanStaticName');
        if($data->error()) {
            throw new \Exception($data->error());
        }
        $vlans = [];
        foreach ($data->fetchAll() as $d) {
            $vlans[] = [
               'vlan_id' => (int)Helper::getIndexByOid($d->getOid()),
               'name' =>  $this->convertHexToString($d->getHexValue()),
            ];
        }
        return $vlans;
    }


    public function run($filter = [])
    {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dot1q.VlanStaticName')->getOid())
        ]));
        return $this;
    }

}

