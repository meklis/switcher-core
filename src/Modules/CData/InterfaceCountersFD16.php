<?php

namespace SwitcherCore\Modules\CData;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCountersFD16 extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    protected $_ifacesHC = [];

    protected function getInterfacesWithIfHcIds($fromCache = true)
    {
        if ($this->_ifacesHC) return $this->_ifacesHC;

        if ($fromCache) {
            if ($interfaces = $this->getCache('interfaces_with_hc_id', true)) {
                $this->_ifacesHC = $interfaces;
                return $interfaces;
            }
        }
        $oids = [Oid::init($this->oids->getOidByName('if.Name')->getOid())];
        $response = $this->formatResponse($this->snmp->walk($oids));
        if (!isset($response['if.Name'])) {
            throw new \Exception("Not found response by if.Name");
        }

        if ($response['if.Name']->error()) {
            throw new \Exception($response['if.Name']->error());
        }

        //Fetching physical interfaces
        $physicalIfaces = [];
        foreach ($this->model->getExtraParamByName('interfaces') as $iface) {
            $physicalIfaces[$iface['xid']] = $iface;
        }

        $interfaces = [];
        foreach ($response['if.Name']->fetchAll() as $iface) {
            $id = Helper::getIndexByOid($iface->getOid());
            if (isset($physicalIfaces[$id])) {
                $interfaces[$id]['interface'] = $physicalIfaces[$id];
                $interfaces[$id]['interface']['_hc_id'] = $id;
                continue;
            }
            $if = $this->parseInterface($iface->getValue());
            $interfaces[$id]['interface'] = $if;
            $interfaces[$id]['interface']['_hc_id'] = $id;
        }
        $this->_ifacesHC = $interfaces;
        $this->setCache('interfaces_with_hc_id', $interfaces, 3600, true);
        return $interfaces;
    }

    protected function isHcInterfaceExist($interface)
    {
        if ($interfaces = $this->getCache('interfaces_with_hc_id', true)) {
            $data = array_filter($interfaces, function ($e) use ($interface) {
                return $e['interface']['name'] == $interface['name'];
            });
            return count($data) > 0;
        }
        return false;
    }

    function getInterfaceOids($interface_id, $oids)
    {
        return array_map(function ($e) use ($interface_id) {
            return Oid::init($e->getOid() . $interface_id);
        }, $oids);
    }

    public function run($params = [])
    {
        $global_oids = $this->getInterfaceCountersOids();

        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $whole_ifaces = $this->getInterfacesWithIfHcIds($this->isHcInterfaceExist($interface));
            $filtered = array_values(array_filter($whole_ifaces, function ($i) use ($interface) {
                return $i['interface']['name'] == $interface['name'];
            }));
            if (count($filtered) == 0) {
                throw new \Exception("Not found interface id for HC mib");
            }
            $filtered[$filtered[0]['interface']['_hc_id']] = $filtered[0];
            $suffix = '.' . $filtered[0]['interface']['_hc_id'];
            unset($filtered[0]);

            $oids = $this->getInterfaceOids($suffix, $global_oids);

            $this->response = $this->process($this->formatResponse($this->snmp->get($oids)), $filtered);
            return $this;
        }

        if ($params['interface_type'] == 'ONU') {
            $onus = [];
            $whole_ifaces = $this->getInterfacesWithIfHcIds();
            foreach ($whole_ifaces as $xid => $port) {
                if ($port['interface']['type'] == 'ONU') {
                    $onus[$xid] = $port;
                }
            }
            $oids = $this->getInterfaceOids('', $global_oids);

            $this->response = $this->process($this->formatResponse($this->snmp->walkNext($oids)), $onus);
        } elseif ($params['interface_type'] == 'PHYSICAL') {
            $response = [];
            foreach ($this->model->getExtraParamByName('interfaces') as $iface) {
                $item[$iface['xid']]['interface'] = $iface;
                $oids = $this->getInterfaceOids('.' . $iface['xid'], $global_oids);
                $response[] = current($this->process($this->formatResponse($this->snmp->get($oids)), $item));
            }
            $this->response = array_values($response);
        } else {
            $whole_ifaces = $this->getInterfacesWithIfHcIds();
            $oids = $this->getInterfaceOids('', $global_oids);

            $this->response = $this->process($this->formatResponse($this->snmp->walkNext($oids)), $whole_ifaces);
        }
        return $this;
    }


    /**
     * @param WrappedResponse[] $response
     * @return array
     */
    private function process($response, $interfaces)
    {
        $data = [];
        foreach ($response as $name => $dt) {
            if ($dt->error()) {
                throw new \SNMPException($dt->error());
            }
            $name = Helper::fromCamelCase(str_replace(["if.HC", "if"], "", $name));
            foreach ($dt->fetchAll() as $resp) {
                $xid = Helper::getIndexByOid($resp->getOid());
                if (!isset($interfaces[$xid])) continue;
                $data[$xid]['interface'] = $interfaces[$xid]['interface'];
                $data[$xid][$name] = $resp->getValue();

            }
        }
        return array_values($data);
    }

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
    }
}