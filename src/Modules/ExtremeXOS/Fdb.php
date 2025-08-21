<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\FdbDot1BridgeWithConsole;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1BridgeWithConsole {
    use InterfacesTrait;

    protected function getFromSNMP() {
        $oids[] = Oid::init($this->oids->getOidByName('extreme.vlanIfVlanId')->getOid());
        $oids[] = Oid::init($this->oids->getOidByName('extreme.fdbMacExosFdbStatus')->getOid());
        $oids[] = Oid::init($this->oids->getOidByName('extreme.fdbMacExosFdbPortIfIndex')->getOid());
        $res = $this->formatResponse($this->snmp->walk($oids));
        if($res['extreme.vlanIfVlanId']->error()) {
            throw new \Exception("Returned error {$res['extreme.vlanIfVlanId']->error()} from {$res['extreme.vlanIfVlanId']->getRaw()->ip}");
        }
        if($res['extreme.fdbMacExosFdbStatus']->error()) {
            throw new \Exception("Returned error {$res['extreme.fdbMacExosFdbStatus']->error()} from {$res['extreme.fdbMacExosFdbStatus']->getRaw()->ip}");
        }
        if($res['extreme.fdbMacExosFdbPortIfIndex']->error()) {
            throw new \Exception("Returned error {$res['extreme.fdbMacExosFdbPortIfIndex']->error()} from {$res['extreme.fdbMacExosFdbPortIfIndex']->getRaw()->ip}");
        }
        $vlanIdAssoc = [];
        foreach ($res['extreme.vlanIfVlanId']->fetchAll() as $val) {
            $id = Helper::getIndexByOid($val->getOid());
            $vlanIdAssoc[$id] = $val->getValue();
        }
        foreach ($res['extreme.fdbMacExosFdbStatus']->fetchAll() as $val) {
            $data = Helper::oid2VlanMac($val->getOid());
            $statuses["{$data['vid']}-{$data['mac']}"] = $val->getParsedValue();
        }
        foreach ($res['extreme.fdbMacExosFdbPortIfIndex']->fetchAll() as $val) {
            $data = Helper::oid2VlanMac($val->getOid());
            $ports["{$data['vid']}-{$data['mac']}"] = $val->getValue();
        }   

        $result = [];
        foreach ($statuses as $key => $status) {
            list($vlanId, $macAddr) = explode("-", $key);
            if(!isset($ports[$key])) continue;
            if(!(int)$ports[$key])  continue;
            try {
                $result[] = [
                    'interface' => $this->parseInterface($ports[$key]),
                    'vlan_id' => isset($vlanIdAssoc[$vlanId]) ? (int)$vlanIdAssoc[$vlanId] : null,
                    'mac_address' => $macAddr,
                    'status' => $status,
                ];
            } catch (\Throwable $e) {}
        }

        return $result;
    }

    protected function getFromConsole(array $filter) {
        $cmd = 'show fdb ';
        if($filter['vlan_id']) {
            throw new \Exception('Search by vlan_id is not supported yet');
        }
        if($filter['mac']) {
            $mac = Helper::formatMac($filter['mac']);
            $cmd .= $mac;
        } elseif($filter['interface']) {
            $port = $this->parseInterface($filter['interface'])['_dot1q_id'];
            $cmd .= 'ports ' . $port;
        }
        $res = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
        if(!$res['success']) throw new \Exception("Error while running command {$cmd}");
        $res = explode("\n", $res['output']);

        $response = [];
        foreach($res as $line) {
            if(preg_match('/^(..:..:..:..:..:..)\s+[A-Za-z0-9]+\((\d\d\d\d)\)\s+\d\d\d\d\s+([a-zA-Z]+)\s+[a-zA-Z]+\s+(\d{1,4})/i', trim($line), $m)) {
                $mac = Helper::formatMac($m[1]);
                $vlan = $m[2];
                $status = (($m[3] === 's' || $m[3] === 'p') ? 'STATIC' : 'LEARNED');
                $iface = $this->parseInterface($m[4], '_dot1q_id');

                $response[] = [
                    'interface' => $iface,
                    'vlan_id' => $vlan,
                    'mac_address' => $mac,
                    'status' => $status,
                ];
            }
        }
        return $response;
    }

}