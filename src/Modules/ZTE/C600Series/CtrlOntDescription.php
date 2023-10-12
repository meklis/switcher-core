<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class CtrlOntDescription extends ModuleAbstract
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = true ;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);
        if(!preg_match('/^.*([0-9])\/([0-9]{1,4}):([0-9]{1,3})$/', $iface['name'], $match)) {
            throw new \InvalidArgumentException("Error parse ONT interface");
        }
        $description = str_replace(' ', '_', $filter['description']);
        $this->checkSnmpRespError($this->snmp->set(
            Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid() . ".{$iface['_oid_id']}")
                ->setType('StringValue')
                ->setValue($description)
        ));
        return $this;
    }

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}

