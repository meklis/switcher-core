<?php

namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Modules\AbstractModule;

class SetDeviceName extends AbstractModule
{
    public function run($params = [])
    {

        if(!isset($params['name'])) {
            $params['name'] = '';
        }
        $aliasOid = $this->oids->getOidByName('sys.Name')->getOid();
        $name = str_replace([" "], '_', $params['name']);
        $resp = $this->snmp->set(
            Oid::init($aliasOid,
                false,
                PoollerRequest::TypeOctetStringValue,
                $name
            ));
        foreach ($resp as $r) {
            if($r->error) {
                throw new \Exception("Error update device name - {$r->error}");
            }
        }
        $this->response = [
            'name' => $name,
            'response' => $resp,
        ];
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}