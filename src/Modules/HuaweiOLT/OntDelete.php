<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntDelete extends HuaweiOLTAbstractModule
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

        //Remove service port
        $this->console->exec("undo service-port port {$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']} ont {$iface['_onu']}", true, "\<cr\>");
        $this->console->exec("", true, "Are you sure to release service virtual port");
        $this->console->exec("y");

        //Remove ONT
        $this->console->exec("interface gpon {$iface['_shelf']}/{$iface['_slot']}");
        $resp = $this->console->exec("ont delete {$iface['_port']} {$iface['_onu']}");
        if(strpos($resp, "Failure") !== false) {
            throw new \Exception("Error delete, resp from device: " . $resp);
        }
        return $this;
    }
}

