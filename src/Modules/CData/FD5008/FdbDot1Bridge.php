<?php


namespace SwitcherCore\Modules\CData\FD5008;

use Exception;
use InvalidArgumentException;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class FdbDot1Bridge extends \SwitcherCore\Modules\General\Switches\FdbDot1Bridge {
    use InterfacesTrait;

    protected $filter_vlanid = false;
    protected $filter_mac = false;
    protected function formate() {
        $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show mac-address all']])->getPretty();
        if(!$output[0]) throw new \Exception('Error calling telnet console in the module Fdb');

        $d = explode("------------------------------------------------------------------------", $output[0]['output']);
        if(!isset($d[2])) throw new \Exception('Error calling telnet console in the module Fdb');
        $strings = explode("\n", $d[2]);

        $pretties = [];
        foreach($strings as $string) {
            if(preg_match('/^\s*([A-Z0-9]{2}:[A-Z0-9]{2}:[A-Z0-9]{2}:[A-Z0-9]{2}:[A-Z0-9]{2}:[A-Z0-9]{2})\s*(\d{1,4})\s*([a-z]*\d{1,3}\/\d{1,3}\/\d{1,3})/', $string, $m)) {
                $mac = $m[1];
                $vlan = intval($m[2]);
                $port = $m[3];
                if($this->filter_vlanid && intval($this->filter_vlanid) !== $vlan) continue;
                if($this->filter_mac && $this->filter_mac !== $mac) continue;
                if($iface = $this->parseInterface($port, 'name')) {
                    $pretties[] = [
                        'interface' => $iface,
                        'vlan_id' => intval($vlan),
                        'mac_address' => $mac,
                        'status' => 'LEARNED',
                    ];
                }
            }
        }
        return array_values($pretties);
    }

    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$fdb) {
                if($fdb['interface']['id'] != $interface['id']) {
                    unset($formated[$num]);
                }
            }
        }
        if($filter['vlan_id']) {
            foreach ($formated as $num=>$fdb) {
                if($fdb['vlan_id'] != $filter['vlan_id']) {
                    unset($formated[$num]);
                }
            }
        }
        if($filter['mac']) {
            foreach ($formated as $num=>$fdb) {
                if($fdb['mac_address'] != $filter['mac']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    function getPretty() {
        return $this->formate();
    }

    public function run($filter = []) {
        Helper::prepareFilter($filter);

        if($filter['vlan_id']) {
            $this->filter_vlanid = $filter['vlan_id'];
        }
        if($filter['mac']) {
            $this->filter_mac = $filter['mac'];
        }
 
        return $this;
    }
}
