<?php


namespace SwitcherCore\Modules\CData\FD5008;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class PvidDot1q extends \SwitcherCore\Modules\General\Switches\PvidDot1q {
    use InterfacesTrait;

    protected function formate() {
        $ifaces = $this->getInterfacesIds();
        $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show port brief all']])->getPretty();
        $strings = explode("\n", explode("-----------------------------------------------------------------------------------------------------", $output[0]['output'])[2]);
        array_shift($strings);
        array_pop($strings);
        $indexes = [];
        foreach($strings as $string) {
            if(preg_match('/(gigabitethernet|fastethernet|xpon)\s*(\d{1,3}\/\d{1,3}\/\d{1,3})\s*(\d{1,4})\s*(enable|\-|disable)\s*(\d{3,4}|\-)\s*(full|half|\-)\s*.*?\s*.*?(enable|\-|disable)\s*(off|on)/', $string, $m)) {
                $fullname = $m[1] . $m[2];
                foreach($ifaces as $num => $iface) {
                    if($iface['_fullname'] === $fullname) {
                        $indexes[$iface['id']]['interface'] = $iface;
                        $indexes[$iface['id']]['pvid'] = $m[3];
                    }
                }
            }
        }
        return array_values($indexes);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        Helper::prepareFilter($filter);
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

    public function run($filter = []) {
        return $this;
    }
}