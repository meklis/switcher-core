<?php


namespace SwitcherCore\Modules\General\Switches;


use InvalidArgumentException;
use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;

abstract class StatePortControl extends AbstractInterfaces
{
    function run($params = [])
    {
        $interfaces = $this->getModule('link_info')->run(['interface' => $params['interface']])->getPrettyFiltered(['interface' => $params['interface']]);
        $interface = $this->parseInterface($params['interface']);

        $Oid = $this->oids->getOidByName('if.AdminStatus')->getOid() . ".{$interface['_snmp_id']}";
        //1: Enabled, 2: Disabled
        $state = null;
        if($params['state'] == 'enable') {
            $state = 1;
        }
        if($params['state'] == 'disable') {
            $state = 2;
        }
        $resp = $this->snmp->set(
            Oid::init($Oid,
                false,
                PoollerRequest::TypeIntegerValue,
                $state
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update interface state, interface={$interface['name']} - {$r->error}");
            }
        }
        $this->response = [
            'interface' => $interface,
            'state' => $params['state'],
            'response' => $resp,
        ];
        return $this;
    }

    protected function formate()
    {
        return $this->response;
    }

    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }
}