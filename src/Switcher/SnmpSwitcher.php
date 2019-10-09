<?php


namespace SnmpSwitcher\Switcher;


class SnmpSwitcher extends Switcher
{
    function getLinkInfo($port = 0, $ethernetOnly=true) {
        $type = '';
        if($ethernetOnly) {
            $type = 'GE,FE,-';
        }
        return $this->getParser('link')->walk([
            'port' => $port,
        ])->getPrettyFiltered(['type' => $type, 'port'=>$port]);
    }
    function getCounters($port = 0) {
        return $this->getParser('counters')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getErrors($port = 0) {
        return $this->getParser('errors')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getRmon($port) {
        return $this->getParser('rmon')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getFDB($port = 0, $vlan = 0, $mac = "")
    {
        return $this->getParser('fdb')->walk([
            'mac' => $mac,
            'vlan_id' => $vlan,
        ])->getPrettyFiltered([
            'port' => $port,
        ]);
    }
    function getVlans($vlanId = 0) {
        return $this->getParser('vlan')->walk(['vlan_id'=>$vlanId])->getPrettyFiltered();
    }
    function getVlansByPort($show_port = 0) {
        $parser =  $this->getParser('vlan');
        $data = $parser->walk()->getPrettyFiltered();
        $indexes = $parser->getIndexes();
        $response = [];
        foreach ($indexes as $index=>$port) {
            if($show_port && $port != $show_port) continue;
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(in_array($port, $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port, $d['ports']['egress'])) $egress_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(isset($d['ports']['tagged'])) {
                    if(in_array($port, $d['ports']['tagged'])) $tagged_vlans[] = [
                        'name' => $d['name'],
                        'id' => $d['id'],
                    ];
                }
                if(in_array($port, $d['ports']['forbidden'])) $forbidden_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
            }
            $response[] = [
                'port' => $port,
                'untagged' => $untagged_vlans,
                'tagged' => $tagged_vlans,
                'egress' => $egress_vlans,
                'forbidden' => $forbidden_vlans,
            ];
        }
        return $response;
    }
    function getPVID($port = 0) {
        return $this->getParser('pvid')->walk(['port'=>$port])->getPretty();
    }
    function getCableDiag($port = 0) {
        return $this->getParser('cable_diag')->walk(['port'=>$port])->getPretty();
    }
    function resetCounters() {
        return $this->getParser('reset_counters')->walk()->getPretty();
    }
    function rebootDevice() {
        return $this->getParser('reboot_device')->walk()->getPretty();
    }
}