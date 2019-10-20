<?php


namespace SwitcherCore\Switcher;

/**
 * Class SnmpSwitcher
 * @package SwitcherCore\Switcher
 */
class SnmpSwitcher
{
    /**
     * @var Switcher
     */
    protected $sw = null;
    function __construct(Switcher $switcher)
    {
        $this->sw = $switcher;

    }

    function getLinkInfo($port = 0, $ethernetOnly=true) {
        $type = '';
        if($ethernetOnly) {
            $type = 'GE,FE,-';
        }
        return $this->sw->getModule('link')->walk([
            'port' => $port,
        ])->getPrettyFiltered(['type' => $type, 'port'=>$port]);
    }
    function getCounters($port = 0) {
        return $this->sw->getModule('counters')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getErrors($port = 0) {
        return $this->sw->getModule('errors')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getRmon($port) {
        return $this->sw->getModule('rmon')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getFDB($port = 0, $vlan = 0, $mac = "")
    {
        return $this->sw->getModule('fdb')->walk([
            'mac' => $mac,
            'vlan_id' => $vlan,
        ])->getPrettyFiltered([
            'port' => $port,
        ]);
    }
    function getVlans($vlanId = 0) {
        return $this->sw->getModule('vlan')->walk(['vlan_id'=>$vlanId])->getPrettyFiltered();
    }
    function getVlansByPort($show_port = 0) {
        $parser =  $this->sw->getModule('vlan');
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
        return $this->sw->getModule('pvid')->walk(['port'=>$port])->getPretty();
    }
    function getCableDiag($port = 0) {
        return $this->sw->getModule('cable_diag')->walk(['port'=>$port])->getPretty();
    }
    function getSystemInfo() {
        return $this->sw->getModule('system')->walk()->getPretty();
    }
}