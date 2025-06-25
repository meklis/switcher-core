<?php


namespace SwitcherCore\Modules\CData\FD5008;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class LinkInfo extends \SwitcherCore\Modules\General\Switches\LinkInfo {
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
            if(preg_match('/(gigabitethernet|fastethernet|xpon)\s*(\d{1,3}\/\d{1,3}\/\d{1,3})\s*\d{1,4}\s*(enable|\-|disable)\s*(\d{3,4}|\-)\s*(full|half|\-)\s*.*?\s*.*?(enable|\-|disable)\s*(off|on)/', $string, $m)) {
                $fullname = $m[1] . $m[2];
                switch($m[1]) {
                    case 'gigabitethernet': $type = 'GE'; break;
                    case 'fastethernet': $type = 'FE'; break;
                    case 'xpon': $type = 'PON'; break;
                    default: $type = null;
                }

                foreach($ifaces as $num => $iface) {
                    if($iface['_fullname'] === $fullname) {
                        $index = $iface['id'];
                        $indexes[$index]['interface'] = $iface;
                        $indexes[$index]['oper_status'] = ($m[7] === 'on') ? "Up" : "Down";
                        $speed = ($m[4] === '-') ? 'Down' : ($m[4] >= 1000 ? strval($m[4] / 1000) . 'G' : $m[4]);
                        if($speed === 'Down' && $indexes[$index]['oper_status'] === 'Up') $speed = null;
                        $indexes[$index]['nway_status'] = $speed;
                        $indexes[$index]['admin_state'] = ($m[6] === 'enable') ? 'Enabled' : 'Disabled';
                        $indexes[$index]['medium_type'] = null;
                        $indexes[$index]['type'] = $type;
                        if($speed && $speed !== 'Down') {
                            if($m[5] === 'full') $indexes[$index]['nway_status'] .= "-" . 'Full';
                            if($m[5] === 'half') $indexes[$index]['nway_status'] .= "-" . 'Half';
                        }
                        $indexes[$index]['last_change'] = null;
                        break;
                    }
                }
            }
        }

        $oidObject = Oid::init($this->oids->getOidByName('if.LastChange')->getOid());
        $resp = $this->formatResponse($this->snmp->walk([$oidObject]));
        $snmp_last_change = !$resp['if.LastChange']->error() ? $resp['if.LastChange']->fetchAll() : [];
        foreach($snmp_last_change as $index) {
            if (!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
            if ($index->getValue()) {
                $indexes[Helper::getIndexByOid($index->getOid())]['last_change'] = $index->getValueAsTimeTicks();
            }
        }

        return $indexes;
    }

    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();
        if ($filter['type']) {
            $types = explode(",", $filter['type']);
            foreach ($response as $num => $resp) {
                if (!isset($resp['type'])) {
                    unset($response[$num]);
                    continue;
                }
                if (!in_array($resp['type'], $types)) {
                    unset($response[$num]);
                }
            }
        }
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($response as $num => $resp) {
                if (!isset($resp['interface'])) {
                    unset($response[$num]);
                    continue;
                }
                if ($interface['id'] != $resp['interface']['id']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }

    public function run($filter = []) {
        return $this;
    }
}

