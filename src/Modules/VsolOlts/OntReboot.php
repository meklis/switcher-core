<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReboot extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = true ;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);
        $this->console->exec("epon reboot onu interface {$iface['name']}", true, "Are you sure");
        $this->console->exec("y");
        return $this;
    }
}

