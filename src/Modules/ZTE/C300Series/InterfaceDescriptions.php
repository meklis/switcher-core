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
            if ($parsed['type'] == 'ONU' && $parsed['_technology'] == 'gpon') {
                $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponName')->getOid() . ".{$parsed['_oid_id']}");
                $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } elseif ($parsed['type'] == 'ONU' && $parsed['_technology'] == 'epon') {
                $oids[] = Oid::init($this->oids->getOidByName('epon.ont.EponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } else {
                throw new \InvalidArgumentException("Allow only ONT");
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
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']] = [
                        'interface' => $iface,
                        'description' => $this->prettyDescription($resp->getHexValue()),
                        '_description' => $this->prettyDescription($resp->getHexValue()),
                    ];
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
            }
        }
        if (isset($response['gpon.ont.GponName']) && !$response['gpon.ont.GponName']->error()) {
            foreach ($response['gpon.ont.GponName']->fetchAll() as $resp) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']]['_name'] = $this->prettyDescription($resp->getHexValue());
                    if (strpos($this->prettyDescription($resp->getHexValue()), "ONU-") === false) {
                        $data[$iface['id']]['description'] = $this->prettyDescription($resp->getHexValue());
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
            }
        }
        if (isset($response['epon.ont.EponDescription']) && !$response['epon.ont.EponDescription']->error()) {
            foreach ($response['epon.ont.EponDescription']->fetchAll() as $resp) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']] = [
                        'interface' => $iface,
                        'description' => $this->prettyDescription($resp->getHexValue()),
                        '_description' => $this->prettyDescription($resp->getHexValue()),
                        '_name' => null,
                    ];
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
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