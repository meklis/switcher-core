<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntDeleteGpon extends CDataAbstractModuleFD16xxV3
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
        if (!preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $interface['name'], $m)) {
            throw new \Exception("Interface {$filter['interface']} not supported!");
        }
        if(count($m) < 5) {
            throw new \Exception("Allow to reboot only ONTs");
        }
        $this->_exe("interface gpon 0/0");
        $this->_exe("ont delete {$m[4]} {$m[5]}");
        $this->_exe("exit");
        $this->_exe("save");
        $this->response = true;
        return $this;
    }
}

