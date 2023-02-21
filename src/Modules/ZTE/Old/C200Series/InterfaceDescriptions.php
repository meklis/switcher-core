<?php


namespace SwitcherCore\Modules\ZTE\Old\C200Series;




use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class InterfaceDescriptions extends ModuleAbstract
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
                    'description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        if(isset($response['zx.ont.EponDescription']) && !$response['zx.ont.EponDescription']->error()) {
            foreach ($response['zx.ont.EponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                $data[] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        $this->response = $data;
        return  $this;
    }
    private function prettyDescription($descr) {
        $description = "";
        foreach (explode(":", $descr) as $desc) {
            $char = Helper::hexToStr($desc);
            if(!mb_detect_encoding($char, 'ASCII', true)) {
                continue;
            }
            $description .= $char;
        }
        if(str_contains( $description, '$$')) {
            $blocks = explode("$$", $description);
            return $blocks[count($blocks) - 2];
        } else {
            return $description;
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