<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class InterfaceDescriptions extends ModuleAbstract
{
    public function run($params = [])
    {
        if ($params['interface']) {
            $oids = [];
            $parsed = $this->parseInterface($params['interface']);
            if ($parsed['type'] == 'ONU' && $parsed['_technology'] == 'gpon') {
             //   $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponName')->getOid() . ".{$parsed['_oid_id']}");
                $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid() . ".{$parsed['_oid_id']}");
            } else if ($parsed['type'] !== 'ONU') {
                $oids[] = Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$parsed['_xid']}");
                //throw new \InvalidArgumentException("Allow only ONT");
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
            return  $this;
        }

        $oids = [];
        if($params['interface_type'] && $params['interface_type'] == 'ONU') {
            $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid());
        } elseif ($params['interface_type'] && $params['interface_type'] == 'PHYSICAL') {
            $oids[] = Oid::init($this->oids->getOidByName('if.Alias')->getOid());
        } else {
            $oids[] = Oid::init($this->oids->getOidByName('gpon.ont.GponDescription')->getOid());
            $oids[] = Oid::init($this->oids->getOidByName('if.Alias')->getOid());
        }
        $this->response = $this->formatResponse($this->snmp->walk($oids));
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
        $data = [];
        if (isset($this->response['gpon.ont.GponDescription']) && !$this->response['gpon.ont.GponDescription']->error()) {
            foreach ($this->response['gpon.ont.GponDescription']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 1) . "." . Helper::getIndexByOid($resp->getOid()));
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getHexValue()),
                    '_description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        if (isset($this->response['if.Alias']) && !$this->response['if.Alias']->error()) {
            foreach ($this->response['if.Alias']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'xid');
                $data[$iface['id']] = [
                    'interface' => $iface,
                    'description' => $this->prettyDescription($resp->getHexValue()),
                    '_description' => $this->prettyDescription($resp->getHexValue()),
                ];
            }
        }
        return array_values($data);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}