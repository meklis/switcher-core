<?php


namespace SwitcherCore\Modules\CData\FD5008;

use SnmpWrapper\Oid;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Helper;

class VlansDot1q extends \SwitcherCore\Modules\General\Switches\VlansDot1q {
    use InterfacesTrait;
    
    protected $filter_vlan = false;

    protected function formate() {
        $response = [];
        $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show vlan all']])->getPretty();
        if(!$output) throw new \Exception("Console command 'show vlan all' failed");
        $raw_vlans = explode("\n\n", $output[0]['output']);

        $indexes = [];
        foreach ($this->getInterfacesIds() as $iface) {
            $indexes[$iface['_fullname']] = $iface;
        }

        foreach($raw_vlans as $raw_vlan) {
            $strings = explode("\n", $raw_vlan);
            $next_string = '';
            $vlan_id = null;
            $vlan_description = null;
            foreach($strings as $string) {
                if($next_string === 'tagged' || $next_string === 'untagged') {
                    if(preg_match('/^\s*Untagged\s*Ports:\s*?(none)?/', $string, $m)) {
                        if(isset($m[1])) break;
                        $next_string = 'untagged';
                    } else {
                        $res = explode("  ", trim($string));
                        foreach($res as $r) {
                            if(preg_match('/^(gigabitethernet|fastethernet|xpon)\s*(\d{1,3}\/\d{1,3}\/\d{1,3})/', $r, $m)) {
                                $fullname = $m[1] . $m[2];
                                if(isset($indexes[$fullname])) {
                                    if($next_string === 'tagged') {
                                        $response[$vlan_id]['ports']['tagged'][] = $indexes[$fullname];
                                    } else {
                                        $response[$vlan_id]['ports']['untagged'][] = $indexes[$fullname];
                                    }
                                }
                            }
                        }
                    }
                }
                if(preg_match('/^VLAN ID: (\d{1,4})/', $string, $m)) {
                    $vlan_id = $m[1];
                    $response[$vlan_id]['id'] = $vlan_id;
                    $response[$vlan_id]['name'] = 'VLAN ID: ' . $vlan_id;
                }
                if(preg_match('/^\s*Description\s*:\s*?(.*)/', $string, $m)) { 
                    $vlan_description = $m[1];
                    $response[$vlan_id]['name'] = (strlen($vlan_description) > 0 && $vlan_description !== ' ') ? $vlan_description : 'VLAN ID: ' . $vlan_id;
                }
                if(preg_match('/^\s*Tagged\s*Ports:\s*?(none)?/', $string, $m)) {
                    $next_string = 'tagged';
                }
            }
        }
        
        if($this->filter_vlan) {
            foreach($response as $id => $arr) {
                if($this->filter_vlan != $id) unset($response[$id]);
            }
        }
        return array_values($response);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num => $arr) {
                if($interface['id'] != $arr['ports']['tagged']['id']
                    && $interface['id'] != $arr['ports']['untagged']['id']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        if($filter['vlan_id']) {
            if(preg_match('/^\d{1,4}$/', $filter['vlan_id'])) {
                $this->filter_vlan = $filter['vlan_id'];
            }
        }
        return $this;
    }
}
