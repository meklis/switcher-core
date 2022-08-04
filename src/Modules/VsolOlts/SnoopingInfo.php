<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SnoopingInfo extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac_address']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        return array_values($data);
    }

    function getPretty()
    {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $fdb = [];
        foreach ($this->getModule('fdb')->run(['interface'=>null])->getPretty() as $f) {
            $fdb["{$f['vlan_id']}-{$f['mac_address']}"] = $f;
        }
        $resp = [];
        foreach ($this->loadSnoopTable() as $snoop) {
            if(!isset($fdb["{$snoop['vlan_id']}-{$snoop['mac_address']}"])) continue;
            $snoop['interface'] = $fdb["{$snoop['vlan_id']}-{$snoop['mac_address']}"]['interface'];
            $resp[] = $snoop;
        }
        $this->response = $resp;
        return $this;
    }


    public function loadSnoopTable() {
        $oids = array_map(function ($e)  {
            return \SnmpWrapper\Oid::init($e->getOid());
        },$this->oids->getOidsByRegex('^dhcp.snooping\..*'));
        $resp = [];
        foreach ($this->formatResponse($this->snmp->walk($oids)) as $name=>$response) {
            if($response->error()) {
                throw new \Exception("Err get UNI info: {$response->error()}");
            }
            foreach ($response->fetchAll() as $o) {
                $id = Helper::getIndexByOid($o->getOid());
                switch ($name) {
                    case 'dhcp.snooping.bindingMac':
                        $resp[$id]['mac_address'] = strtoupper($response->fetchOne()->getParsedValue());
                        break;
                    case 'dhcp.snooping.bindingIp':
                        $resp[$id]['ip'] = $response->fetchOne()->getParsedValue();
                        break;
                    case 'dhcp.snooping.bindingLease':
                        $resp[$id]['lease_time'] = (int) $response->fetchOne()->getParsedValue();
                        break;
                    case 'dhcp.snooping.bindingType':
                        $resp[$id]['type'] = $response->fetchOne()->getParsedValue();
                        break;
                    case 'dhcp.snooping.bindingVlan':
                        $resp[$id]['vlan_id'] = (int)$response->fetchOne()->getParsedValue();
                        break;
                }
            }
        }
        return array_values($resp);
    }
}

