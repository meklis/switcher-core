<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReboot extends HuaweiOLTAbstractModule
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

        $oid = $this->oids->getOidByName('ont.controlReset')->getOid() . ".{$iface['xid']}";
        $resp = $this->snmp->set(Oid::init($oid, false, 'Integer', 1));
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        return $this;
    }
}

