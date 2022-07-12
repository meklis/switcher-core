<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class InterfacesList extends HuaweiOLTAbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

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
        $data = $this->getResponseByName('ont.config.sn',$this->response);
        $response = [];
        foreach ($data->fetchAll() as $d) {
            $xid = Helper::getIndexByOid($d->getOid(),1) . "." . Helper::getIndexByOid($d->getOid());
            $iface = $this->parseInterface($xid);
            $iface['_sn_hex'] = $d->getHexValue();
            $response[] = $iface;
        }
        return $response;
    }


    public function run($filter = [])
    {
        $oid = $this->oids->getOidByName('ont.config.sn');
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oid = $oid->getOid() . "." . $iface['xid'];
            $this->response = $this->formatResponse($this->snmp->get([Oid::init($oid)]));
        } else {
            $oid = $oid->getOid();
            $this->response = $this->formatResponse($this->snmp->walk([Oid::init($oid)]));
        }
        return $this;
    }

}

