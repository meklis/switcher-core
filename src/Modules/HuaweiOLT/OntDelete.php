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
        //Eol of huawei_ma helper is "\r\n", and MA56xx/MA58xx handles CR and LF as two
        //separate 'Enter', so exec() presses Enter twice - first executes the command,
        //second answers '{ <cr>|gemport<K> }:' and device asks for confirmation.
        //Any additional Enter here answers confirmation with default 'n' and cancels removing
        $this->console->exec("undo service-port port {$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']} ont {$iface['_onu']}", true, ".*\(y\/n\)\[n\]");
        $this->console->exec("y");
        //Remove ONT
        $this->console->exec("interface {$iface['_technology']} {$iface['_shelf']}/{$iface['_slot']}");
        $resp = $this->console->exec("ont delete {$iface['_port']} {$iface['_onu']}");
        if(strpos($resp, "Failure") !== false) {
            throw new \Exception("Error delete, resp from device: " . $resp);
        }
        return $this;
    }
}

