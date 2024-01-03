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
            $response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $descriptionOid = $this->oids->getOidByName('gpon.ont.GponDescription');
            $nameOid = $this->oids->getOidByName('gpon.ont.GponName');

            $ports = $this->getModule('pon_ports_list')->run([])->getPrettyFiltered([]);
            $oids = [];
            foreach ($ports as $port) {
                if($port['_technology'] !== 'gpon') continue;
                $oids[] = \SnmpWrapper\Oid::init("{$descriptionOid->getOid()}.{$port['_oid_id']}");
                $oids[] = \SnmpWrapper\Oid::init("{$nameOid->getOid()}.{$port['_oid_id']}");
            }
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
            $block = -1;
            if(isset($params['_description_block_index']) && is_numeric($params['_description_block_index'])) {
                $block = $params['_description_block_index'];
            }
            foreach ($response['epon.ont.EponDescription']->fetchAll() as $resp) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    $data[$iface['id']] = [
                        'interface' => $iface,
                        'description' => $this->prettyDescription($resp->getHexValue(), $block),
                        '_description' => $this->convertHexToString($resp->getHexValue()),
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

    private function prettyDescription($descr, $block = -1)
    {
        $descr = $this->convertHexToString($descr);
        if (str_contains($descr, '$$')) {
            $blocks = explode("$$", $descr);
            if($block != -1 && isset($blocks[$block])) {
                return $blocks[$block];
            } else {
                return $blocks[count($blocks) - 1];
            }
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