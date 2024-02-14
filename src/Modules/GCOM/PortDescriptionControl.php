<?php


namespace SwitcherCore\Modules\GCOM;


use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\GCOM\GCOMAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class PortDescriptionControl extends GCOMAbstractModule
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
        $description = str_replace(' ', '_', $filter['description']);
        $this->checkSnmpRespError($this->snmp->set(
            Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$iface['xid']}")
                ->setType('StringValue')
                ->setValue($description)
        ));
        return $this;
    }

}

