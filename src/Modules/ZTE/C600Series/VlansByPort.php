<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Helper;

class VlansByPort extends ModuleAbstract {
    function getPretty() {
        return array_values($this->response);
    }

    function getPrettyFiltered($filter = []) {
        return array_values($this->response);
    }

    public function run($filter = []) {
        Helper::prepareFilter($filter);

        $res = $this->getModule('vlans')->run()->getPrettyFiltered();
        $result = [];
        foreach($res as $vlan) {
            $cmd = 'show vlan ' . $vlan['id'];
            $r = $this->getModule('console_command')->run(['command' => $cmd])->getPrettyFiltered();
            if(!$r['success']) throw new \Exception("Error while running command {$cmd}");
            $r = explode("\n", $r['output']);
            
            $next = false;
            foreach($r as $line) {
                $line = trim($line);
                if($next) {
                    if(preg_match('/^sg(\d)+$/', $line, $m)) {
                        $iface = $this->parseInterface($m[0]);
                        $result[$iface['id']]['interface'] = $iface;

                        if(!isset($result[$iface['id']]['ports']['egress'])) $result[$iface['id']]['ports']['egress'] = [];
                        if(!isset($result[$iface['id']]['ports']['tagged'])) $result[$iface['id']]['ports']['tagged'] = [];
                        if(!isset($result[$iface['id']]['ports']['untagged'])) $result[$iface['id']]['ports']['untagged'] = [];
                        if(!isset($result[$iface['id']]['ports']['forbidden'])) $result[$iface['id']]['ports']['forbidden'] = [];

                        $result[$iface['id']]['ports']['egress'][] = $vlan;
                        $result[$iface['id']]['ports'][$next][] = $vlan;
                        continue;
                    }

                    if (preg_match('/^(vport|xgei)-(\d+)\/(\d+)\/(.+)$/', $line, $m)) {
                        $type = ($m[1] === 'vport') ? 'gpon_olt' : $m[1];
                        $shelf = $m[2]; 
                        $slot = $m[3];

                        $rest = $m[4];
                        if($type === 'xgei') {
                            preg_match('/([\d,-]+)$/', $rest, $m);
                            $items = explode(',', $m[0]);
                            foreach($items as $item) {
                                if (strpos($item, '-') !== false) {
                                    [$start, $end] = explode('-', $item);
                                } else {
                                    $start = $end = $item;
                                }
                                for ($i = (int) $start; $i <= (int) $end; $i++) {
                                    $entry = "$type-$shelf/$slot/$i";
                                    
                                    $iface = $this->parseInterface($entry);
                                    $result[$iface['id']]['interface'] = $iface;
                                    
                                    if(!isset($result[$iface['id']]['ports']['egress'])) $result[$iface['id']]['ports']['egress'] = [];
                                    if(!isset($result[$iface['id']]['ports']['tagged'])) $result[$iface['id']]['ports']['tagged'] = [];
                                    if(!isset($result[$iface['id']]['ports']['untagged'])) $result[$iface['id']]['ports']['untagged'] = [];
                                    if(!isset($result[$iface['id']]['ports']['forbidden'])) $result[$iface['id']]['ports']['forbidden'] = [];

                                    $result[$iface['id']]['ports']['egress'][] = $vlan;
                                    $result[$iface['id']]['ports'][$next][] = $vlan;
                                }
                            }
                            continue;
                        }

                        preg_match_all('/(\d+)\.([^,]+(?:,[^,]+)*?)(?=,\d+\.|$)/', $rest, $blocks, PREG_SET_ORDER);
                        foreach ($blocks as $block) {
                            $port = $block[1];
                            $rangePart = $block[2];
                            if (strpos($rangePart, ':') !== false) {
                                [$rangePart, $onu_value] = explode(':', $rangePart, 2);
                            } else {
                                $onu_value = null;
                            }
                            $ranges = explode(',', $rangePart);
                            foreach ($ranges as $rr) {
                                if (strpos($rr, '-') !== false) {
                                    [$start, $end] = explode('-', $rr);
                                } else {
                                    $start = $end = $rr;
                                }
                                for ($i = (int)$start; $i <= (int)$end; $i++) {
                                    $entry = "$type-$shelf/$slot/$port.$i";
                                    if($onu_value !== null) $entry .= ':' . $onu_value;

                                    $iface = $this->parseInterface($entry);
                                    $result[$iface['id']]['interface'] = $iface;
                                    
                                    if(!isset($result[$iface['id']]['ports']['egress'])) $result[$iface['id']]['ports']['egress'] = [];
                                    if(!isset($result[$iface['id']]['ports']['tagged'])) $result[$iface['id']]['ports']['tagged'] = [];
                                    if(!isset($result[$iface['id']]['ports']['untagged'])) $result[$iface['id']]['ports']['untagged'] = [];
                                    if(!isset($result[$iface['id']]['ports']['forbidden'])) $result[$iface['id']]['ports']['forbidden'] = [];

                                    $result[$iface['id']]['ports']['egress'][] = $vlan;
                                    $result[$iface['id']]['ports'][$next][] = $vlan;
                                }
                            }
                        }
                    }
                }
                if($line === 'port(untagged):') {
                    $next = 'untagged';
                    continue;
                }
                if($line === 'port(tagged):') {
                    $next = 'tagged';
                    continue;
                }
            }
        }
        $this->response = $result;
        return $this;
    }
}