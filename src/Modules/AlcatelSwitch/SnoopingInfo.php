<?php


namespace SwitcherCore\Modules\AlcatelSwitch;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\AlcatelSwitch\InterfacesTrait;

class SnoopingInfo extends AbstractModule {

    use InterfacesTrait;

    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        $data = $this->getPretty();
        if(count($data) === 0) return [];
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac_address']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac_address']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        if ($filter['ip']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['ip'] == $filter['ip'];
            });
        }
        return array_values($data);
    }

    function getPretty() {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = []) {
        $cmd = 'show ip dhcp snooping binding';
        if(isset($filter['interface'])) {
            $iface = $this->parseInterface($filter['interface']);
            $cmd .= ' ethernet ' . $iface['name'];
        } elseif(isset($filter['mac_address'])) {
            $mac = strtolower(Helper::formatMac($filter['mac_address']));
            $cmd .= ' mac-address ' . $mac; 
        } elseif(isset($filter['ip'])) {
            $cmd .= ' ip-address ' . $filter['ip'];
        } elseif(isset($filter['vlan_id'])) {
            $cmd .= ' vlan ' . $filter['vlan_id']; 
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        
        $resp = [];
        foreach($r as $line) {
            $m = [];
            if(preg_match('/^((([0-9a-f]{2}):?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,5})\s+(\w{3,})\s+(\d{1,4})\s+([eg]\d{1,3})/i', trim($line), $m)) {
                $resp[] = [
                    'interface' => $this->parseInterface($m[9], 'name'),
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[8],
                    'ip' => $m[4],
                    'remaining' => (int) $m[6],
                    '_type' => strtoupper($m[7]),
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}

