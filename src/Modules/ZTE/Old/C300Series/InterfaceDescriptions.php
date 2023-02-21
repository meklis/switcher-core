<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class InterfaceDescriptions extends ModuleAbstract
{
    public function run($params = [])
    {
        $oids = [];
        if ($params['interface']) {
            $parsed = $this->parseInterface($params['interface']);
            if ($parsed['technology'] == 'gpon') {
                $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponName')->getOid() . ".{$parsed['_oid_id']}");
                $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } elseif ($parsed['is_onu'] && $parsed['technology'] == 'epon') {
                $oids[] = Oid::init($this->oids->getOidByName('epon.ont.EponDescription')->getOid() . ".{$parsed['_oid_id']}");
            }
        }
        if ($oids) {
            $response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponName')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('epon.ont.EponDescription')->getOid());
            $response = $this->formatResponse($this->snmp->walk($oids));
        }
        $data = [];
        if (isset($response['gpon.ont.GponDescription']) && !$response['gpon.ont.GponDescription']->error()) {
            foreach ($response['gpon.ont.GponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        if (isset($response['gpon.ont.GponName']) && !$response['gpon.ont.GponName']->error()) {
            foreach ($response['gpon.ont.GponName']->fetchAll() as $resp) {
                if (strpos($this->prettyDescription($resp->getHexValue()), "ONU-") === false) {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']] = [
                        'interface' => $iface,
                        'description' => $this->prettyDescription($resp->getHexValue()),
                    ];
                }
            }
        }
        if (isset($response['epon.ont.EponDescription']) && !$response['epon.ont.EponDescription']->error()) {
            foreach ($response['epon.ont.EponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        $this->response = array_values($data);
        return $this;
    }

    private function prettyDescription($descr)
    {
        $descr = $this->convertHexToString($descr);
        if (str_contains($descr, '$$')) {
            $blocks = explode("$$", $descr);
            return $blocks[count($blocks) - 1];
        } else {
            return $descr;
        }
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