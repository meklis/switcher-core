<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReboot extends CDataAbstractModuleFD16xxV3
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
        $oid = $this->oids->getOidByName('ont.action.reboot')->getOid();
        $resp = $this->snmp->set(Oid::init($oid . ".{$interface['id']}", false, 'Integer', 1));
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        return $this;
    }
}

