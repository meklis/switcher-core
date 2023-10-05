<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class CtrlOntDescription extends HuaweiOLTAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);
        $this->console->exec("config");

        $descr = str_replace(['"', "'", ], '_', $filter['description']);

        $this->console->exec("interface gpon {$iface['_shelf']}/{$iface['_slot']}");
        $this->console->exec("ont modify {$iface['_shelf']} {$iface['_onu']} desc \"{$descr}\"");

        return $this;
    }
}

