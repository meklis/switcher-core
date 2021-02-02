<?php


namespace SwitcherCore\Modules\Dlink\Vlan;

use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

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
        if(!$this->module->isExist('vlans')) {
            throw new \Exception("Module vlans is required for working");
        }
        $parser =  $this->module->vlans;
        $data = $parser->run()->getPrettyFiltered();
        $indexes = $parser->getIndexes();
        $response = [];
        foreach ($indexes as $index=>$port) {
            if($filter['port'] && $port != $filter['port']) continue;
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(in_array($port, $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port, $d['ports']['egress'])) $egress_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(isset($d['ports']['tagged'])) {
                    if(in_array($port, $d['ports']['tagged'])) $tagged_vlans[] = [
                        'name' => $d['name'],
                        'id' => $d['id'],
                    ];
                }
                if(in_array($port, $d['ports']['forbidden'])) $forbidden_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
            }
            $response[] = [
                'port' => $port,
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
