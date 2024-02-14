<?php


namespace SwitcherCore\Modules\GCOM;


use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\GCOM\GCOMAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class UniInterfacesControlAdminStatus extends GCOMAbstractModule
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
        return $this->response;
    }


    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);

        $action = null;
        switch ($filter['state']) {
            case 'enable': $action = 2; break;
            case 'disable': $action = 0; break;
        }

        $oid = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.uni.adminStatus')->getOid() . ".{$iface['xid']}.{$filter['num']}")
            ->setType('Integer')
            ->setValue($action);
        $resp = $this->snmp->set($oid);
        foreach ($resp as $r) {
            if($r->getError()) {
                throw new \SNMPException($r->getError());
            }
        }
        $this->response = true;
        return $this;
    }
}

