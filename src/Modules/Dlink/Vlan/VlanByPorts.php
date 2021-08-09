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
        foreach ($indexes as $index=>$port) {
            if($filter['port'] && $port['id'] != $filter['port']) continue;
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(in_array($port['id'], $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port['id'], $d['ports']['egress'])) $egress_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(isset($d['ports']['tagged'])) {
                    if(in_array($port['id'], $d['ports']['tagged'])) $tagged_vlans[] = [
                        'name' => $d['name'],
                        'id' => $d['id'],
                    ];
                }
                if(in_array($port['id'], $d['ports']['forbidden'])) $forbidden_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
            }
            $response[] = [
                'port' => $port['id'],
                'interface' => $port,
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
