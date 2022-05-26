<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReset extends BDcomAbstractModule
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
        $oid = $this->oids->getOidByName('ont.action.resetOnu')->getOid();
        $resp = $this->snmp->set(Oid::init($oid, false, 'Integer', 0));
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        return $this;
    }
}

