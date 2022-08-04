<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReset extends VsolOltsAbstractModule
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
        if($iface['type'] !== 'ONU') {
            throw new \InvalidArgumentException("Only onu allow to reset");
        }
        $resp = $this->snmp->set(
            Oid::init(
                $this->oids->getOidByName('action.ont.reset.ponNo')->getOid(),
                false,
                'Integer',
                $iface['_port']
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        $resp = $this->snmp->set(
            Oid::init(
                $this->oids->getOidByName('action.ont.reset.onuNo')->getOid(),
                false,
                'Integer',
                $iface['_onu']
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        $resp = $this->snmp->set(
            Oid::init(
                $this->oids->getOidByName('action.ont.reset.action')->getOid(),
                false,
                'Integer',
                1
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }

        return $this;
    }
}

