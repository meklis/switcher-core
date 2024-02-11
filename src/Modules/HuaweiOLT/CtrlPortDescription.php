<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class CtrlPortDescription extends HuaweiOLTAbstractModule
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
        $descr = str_replace(['"', "'", ], '_', $filter['description']);
        $this->response = $descr;

        $resp = $this->snmp->set(Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$iface['xid']}", false, 'String', $descr));
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        return $this;
    }
}

