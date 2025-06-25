<?php


namespace SwitcherCore\Modules\CData\FD5008;

class VlanByPorts extends \SwitcherCore\Modules\General\Switches\VlanByPorts {
    use InterfacesTrait;

    protected $data;

    protected function formate() {
        return array_values($this->data);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
      return $this->formate();
    }

    public function run($filter = []) {
        $parser =  $this->getModule('vlans');
        $data = $parser->run()->getPrettyFiltered();
        $indexes = $this->getInterfacesIds();
        $response = [];
        $filter_iface = false;
        if($filter['interface']) {
            $filter_iface = $this->parseInterface($filter['interface']);
        }
        foreach ($indexes as $index => $interface) {
            if($filter_iface && $filter_iface['id'] !== $interface['id']) continue;
            
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(isset($d['ports']['untagged']) && in_array($interface, $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                // if(in_array($interface, $d['ports']['egress'])) $egress_vlans[] = [
                //     'name' => $d['name'],
                //     'id' => $d['id'],
                // ];
                if(isset($d['ports']['tagged'])) {
                    if(in_array($interface, $d['ports']['tagged'])) $tagged_vlans[] = [
                        'name' => $d['name'],
                        'id' => $d['id'],
                    ];
                }
                // if(in_array($interface, $d['ports']['forbidden'])) $forbidden_vlans[] = [
                //     'name' => $d['name'],
                //     'id' => $d['id'],
                // ];
            }
            $response[] = [
                'interface' => $interface,
                'untagged' => $untagged_vlans,
                'tagged' => $tagged_vlans,
                'egress' => $egress_vlans,
                'forbidden' => $forbidden_vlans,
            ];
        }
        $this->data = $response;
        return $this;
    }
}
