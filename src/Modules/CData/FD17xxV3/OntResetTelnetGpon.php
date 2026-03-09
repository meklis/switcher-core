<?php


namespace SwitcherCore\Modules\CData\FD17xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntResetTelnetGpon extends CDataAbstractModuleFD17xxV3
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
        $interface = $this->parseInterface($filter['interface']);
        if(!$interface['id'] || !isset($interface['_onu'])) {
            throw new \Exception("Incorrect ONU number");
        }
        $this->_exe("interface gpon {$interface['_shelf']}/{$interface['_slot']}");
        $this->_exe("ont restore-factory {$interface['_port']} {$interface['_onu']}");
        $this->response = true;
        return $this;
    }
}

