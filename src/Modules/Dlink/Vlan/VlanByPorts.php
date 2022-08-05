<?php


namespace SwitcherCore\Modules\Dlink\Vlan;

use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;

class VlanByPorts extends SwitchesPortAbstractModule
{
    protected $data;
    protected function formate() {
        return array_values($this->data);
    }
    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
      return $this->formate();
    }

    public function run($filter = [])
    {
        $parser =  $this->getModule('vlans');
        $data = $parser->run()->getPrettyFiltered();
        $indexes = $parser->getIndexes();
        $response = [];
        foreach ($indexes as $index=>$interface) {
            if(strpos($interface['name'], 'ch') !== false) continue;
            if($filter['interface']) {
                $iface = $this->parseInterface($filter['interface']);
                if($iface['id'] !== $interface['id']) continue;
            }
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(in_array($interface, $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($interface, $d['ports']['egress'])) $egress_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(isset($d['ports']['tagged'])) {
                    if(in_array($interface, $d['ports']['tagged'])) $tagged_vlans[] = [
                        'name' => $d['name'],
                        'id' => $d['id'],
                    ];
                }
                if(in_array($interface, $d['ports']['forbidden'])) $forbidden_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
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
