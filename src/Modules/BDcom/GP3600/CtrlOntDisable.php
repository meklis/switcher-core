<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class CtrlOntDisable extends BDcomAbstractModule
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
        $state = null;
        $oid = $this->oids->getOidByName('ont.action.disable');
        if($filter['state']) {
            foreach ($oid->getValues() as $id=>$value) {
                if($value == $filter['state']) {
                    $state = $id;
                    break;
                }
            }
        }

        $this->checkSnmpRespError($this->snmp->set(
            Oid::init($oid->getOid() . ".{$iface['xid']}")
                ->setType('Integer')
                ->setValue($state)
        ));
        return $this;
    }

    function getPretty()
    {
        return true; // TODO: Change the autogenerated stub
    }
}

