<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use SwitcherCore\Modules\ZTE\ModuleAbstract;

class UniInterfacesControlAdminStatus extends ModuleAbstract
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
     * @return $this|ModuleAbstract
     * @throws \Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);


        if($iface['_technology'] === 'epon') {
            $action = null;
            switch ($filter['state']) {
                case 'enable': $action = 2; break;
                case 'disable': $action = 1; break;
            }
            $oid = \SnmpWrapper\Oid::init($this->oids->getOidByName('epon.uni.admin_state')->getOid() . ".{$iface['_oid_id']}.{$filter['num']}")
                ->setType('Integer')
                ->setValue($action);
        } else {
            $action = null;
            switch ($filter['state']) {
                case 'enable': $action = 1; break;
                case 'disable': $action = 2; break;
            }
            $oid = \SnmpWrapper\Oid::init($this->oids->getOidByName('gpon.uni.admin_state')->getOid() . ".{$iface['_oid_eth_id']}.{$filter['num']}")
                ->setType('Integer')
                ->setValue($action);
        }
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
