<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;

class BgpInfo extends AbstractModule
{
    public function run($params = [])
    {
        $oid = [
            $this->oids->getOidByName('bgp.version'),
            $this->oids->getOidByName('bgp.localAs'),
            $this->oids->getOidByName('bgp.identifier'),
        ];

        $this->response = $this->formatResponse($this->snmp->get($oid));

        foreach ($this->response as $data) {
            if($data->error()) {
                throw new \SNMPException($data->error());
            }
        }

        return $this;
    }

    public function getPretty()
    {

         return [
            'version' => trim($this->getResponseByName('bgp.version')->fetchOne()->getValue()),
            'local_as' => $this->getResponseByName('bgp.localAs')->fetchOne()->getValue(),
            'identifier' => $this->getResponseByName('bgp.identifier')->fetchOne()->getValue(),
         ];
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}