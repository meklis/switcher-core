<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class InterfaceDescriptions extends ModuleAbstract
{
    public function run($params = [])
    {
        $oids = [];
        if ($params['interface']) {
            $parsed = $this->parseInterface($params['interface']);
            if ($parsed['technology'] == 'gpon') {
                $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponName')->getOid() . ".{$parsed['_oid_id']}");
                $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } elseif ($parsed['is_onu'] && $parsed['technology'] == 'epon') {
                $oids[] = Oid::init($this->oids->getOidByName('zx.ont.EponDescription')->getOid() . ".{$parsed['_oid_id']}");
            }
        }
        if ($oids) {
            $response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponName')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('zx.ont.GponDescription')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('zx.ont.EponDescription')->getOid());
            $response = $this->formatResponse($this->snmp->walk($oids));
        }
        $data = [];
        if (isset($response['zx.ont.GponDescription']) && !$response['zx.ont.GponDescription']->error()) {
            foreach ($response['zx.ont.GponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getValue()),
                ];
            }
        }
        if (isset($response['zx.ont.GponName']) && !$response['zx.ont.GponName']->error()) {
            foreach ($response['zx.ont.GponName']->fetchAll() as $resp) {
                if (strpos($this->prettyDescription($resp->getValue()), "ONU-") === false) {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']] = [
                        'interface' => $iface,
                        'description' => $this->prettyDescription($resp->getValue()),
                    ];
                }
            }
        }
        if (isset($response['zx.ont.EponDescription']) && !$response['zx.ont.EponDescription']->error()) {
            foreach ($response['zx.ont.EponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getValue()),
                ];
            }
        }
        $this->response = array_values($data);
        return $this;
    }

    private function prettyDescription($descr)
    {
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