<?php


namespace SwitcherCore\Modules\General\Switches;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

abstract class SetPortDescription extends AbstractInterfaces
{
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

    public function run($filter = [])
    {
        if (!$filter['interface']) {
            throw new \Exception("Arguments interface and description is required");
        }
        if(!isset($filter['description'])) {
            $filter['description'] = '';
        }
        $interface = $this->parseInterface($filter['interface']);
        $aliasOid = $this->oids->getOidByName('if.Alias')->getOid() . ".{$interface['_snmp_id']}";
        $description = str_replace([" "], '_', $filter['description']);
        $resp = $this->snmp->set(
            Oid::init($aliasOid,
                false,
                PoollerRequest::TypeOctetStringValue,
                $description
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update interface description, interface={$interface['name']} - {$r->error}");
            }
        }
        $this->response = [
            'interface' => $interface,
            'description' => $description,
            'response' => $resp,
        ];
        return $this;
    }
}

