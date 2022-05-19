<?php


namespace SwitcherCore\Modules\ZTE\C300Series;




use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class InterfaceDescriptions extends C300ModuleAbstract
{
    public function run($params = [])
    {
        $oids = [];
        if($params['interface']) {
            $parsed = $this->parseInterface($params['interface']);
            if($parsed['technology'] == 'gpon') {
                $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } elseif ($parsed['is_onu'] && $parsed['technology'] == 'epon') {
                $oids[] = Oid::init($this->oids->getOidByName('zx.ont.EponDescription')->getOid() . ".{$parsed['_oid_id']}");
            }
        }
        if($oids) {
            $response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponDescription')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('zx.ont.EponDescription')->getOid());
            $response = $this->formatResponse($this->snmp->walk($oids));
        }
        $data = [];
        if(isset($response['zx.ont.GponDescription']) && !$response['zx.ont.GponDescription']->error()) {
            foreach ($response['zx.ont.GponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                $data[] = [
                    'interface' => $iface,
                    'description' => $resp->getValue(),
                ];
            }
        }
        if(isset($response['zx.ont.EponDescription']) && !$response['zx.ont.EponDescription']->error()) {
            foreach ($response['zx.ont.EponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                $data[] = [
                    'interface' => $iface,
                    'description' => $resp->getValue(),
                ];
            }
        }
        $this->response = $data;
        return  $this;
    }
    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}