<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SwitcherCore\Modules\AbstractModule;

class UniInterfacesControlAdminStatus extends CDataAbstractModuleFD16xxV3
{
    protected $response = null;


    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return true; // TODO: Change the autogenerated stub
    }


    function getPretty()
    {
        return true;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);

        $action = null;
        switch ($filter['state']) {
            case 'enable': $action = 1; break;
            case 'disable': $action = 2; break;
        }

        $oid = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.uni.adminState')->getOid() . ".{$iface['_snmp_id']}.0.{$filter['num']}")
            ->setType('Integer')
            ->setValue($action);
        $resp = $this->snmp->set($oid);
        foreach ($resp as $r) {
            if($r->getError()) {
                throw new \SNMPException($r->getError());
            }
        }
        $this->response = true;
        return $this;
    }
}

