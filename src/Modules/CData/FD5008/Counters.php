<?php


namespace SwitcherCore\Modules\CData\FD5008;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class Counters extends \SwitcherCore\Modules\General\Switches\Counters {
    use InterfacesTrait;

    protected $filter_iface = false;
    protected function formate() {
        $interfaces = [];
        if($this->filter_iface) {
            $interfaces[$this->filter_iface['id']] = $this->filter_iface;
        } else {
            $interfaces = $this->getInterfacesIds();
        }

        $counters = [];
        foreach($interfaces as $num => $iface) {
            $index = $iface['id'];
            $counters[$index] = [
                'interface' => $iface,
                'in_octets' => null,
                'out_octets' => null,
                'in_multicast_pkts' => null,
                'out_multicast_pkts' => null,
                'in_broadcast_pkts' => null,
                'out_broadcast_pkts' => null,
            ];
            $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show statistics ' . $iface['_fullname']]])->getPretty();
            if(!$output[0]) throw new \Exception('Error calling telnet console in the module Counters');

            $d = explode("------------------------------------------------------------------------", $output[0]['output']);
            if(!isset($d[3])) throw new \Exception('Error calling telnet console in the module Counters');
            $strings = explode("\n", $d[3]);
            foreach($strings as $string) {
                if(preg_match('/^\s*Octets\s*:\s*(\d{1,})\s*(\d{1,})/', $string, $m)) {
                    $counters[$index]['in_octets'] = intval($m[1]);
                    $counters[$index]['out_octets'] = intval($m[2]);
                } else if(preg_match('/^\s*BroadcastPackets\s*:\s*(\d{1,})\s*(\d{1,})/', $string, $m)) {
                    $counters[$index]['in_multicast_pkts'] = intval($m[1]);
                    $counters[$index]['out_multicast_pkts'] = intval($m[2]);
                } else if(preg_match('/^\s*MulticastPackets\s*:\s*(\d{1,})\s*(\d{1,})/', $string, $m)) {
                    $counters[$index]['in_broadcast_pkts'] = intval($m[1]);
                    $counters[$index]['out_broadcast_pkts'] = intval($m[2]);
                }
            }
        }

        return array_values($counters);

    }
    
    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        $formated = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($formated as $num=>$val) {
                if($interface['id'] != $val['interface']['id']) {
                    unset($formated[$num]);
                }
            }
        }
        return array_values($formated);
    }

    public function run($params = []) {
        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            if(isset($interface)) $this->filter_iface = $interface;
        }
        return $this;
    }
}