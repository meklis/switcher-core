<?php

namespace SwitcherCore\Modules\CData\FD5008;

class Errors extends \SwitcherCore\Modules\General\Switches\Errors {
    use InterfacesTrait;
    protected $filter_iface = false;

    protected function formate() {
        $interfaces = [];
        if($this->filter_iface) {
            $interfaces[$this->filter_iface['id']] = $this->filter_iface;
        } else {
            $interfaces = $this->getInterfacesIds();
        }

        $errors = [];
        foreach($interfaces as $num => $iface) {
            $index = $iface['id'];
            $errors[$index] = [
                'interface' => $iface,
                'in_errors' => null,
                'out_errors' => null,
                'in_discards' => null,
                'out_discards' => null,
            ];
            $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show statistics ' . $iface['_fullname']]])->getPretty();
            if(!$output[0]) throw new \Exception('Error calling telnet console in the module Errors');

            $d = explode("------------------------------------------------------------------------", $output[0]['output']);
            if(!isset($d[3])) throw new \Exception('Error calling telnet console in the module Errors');
            $strings = explode("\n", $d[3]);
            foreach($strings as $string) {
                if(preg_match('/^\s*Discards\s*:\s*(\d{1,})\s*(\d{1,})/', $string, $m)) {
                    $errors[$index]['in_discards'] = intval($m[1]);
                    $errors[$index]['out_discards'] = intval($m[2]);
                } else if(preg_match('/^\s*Errors\s*:\s*(\d{1,})\s*(\d{1,})/', $string, $m)) {
                    $errors[$index]['in_errors'] = intval($m[1]);
                    $errors[$index]['out_errors'] = intval($m[2]);
                }
            }
        }

        return array_values($errors);
    }

    function getPretty() {
        return $this->formate();
    }
    
    function getPrettyFiltered($filter = []) {
        $errors = $this->formate();
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($errors as $num=>$val) {
                if($interface['id'] != $val['interface']['id']) {
                    unset($errors[$num]);
                }
            }
        }
       return array_values($errors);
    }
    public function run($filter = []) {
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if(isset($interface)) $this->filter_iface = $interface;
        }
        return $this;
    }
}
