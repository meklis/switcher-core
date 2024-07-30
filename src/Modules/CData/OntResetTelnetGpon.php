<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntResetTelnetGpon extends CDataAbstractModule
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
        if(!$interface['id']) {
            throw new \Exception("Incorrect ONU number");
        }
        $this->_exe("interface gpon 0/0");
        $this->_exe("ont restore factory  {$interface['_port']} {$interface['_onu']}");
        $this->response = true;
        return $this;
    }
}

