<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SwitcherCore\Modules\BDcom\BDcomAbstractModule;
use SwitcherCore\Modules\Helper;

class SnoopingInfo extends BDcomAbstractModule {
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        $data = $this->getPretty();
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
        $cmd = 'show ip dhcp-relay snooping binding all';
        if(isset($filter['interface'])) {
            $iface = $this->parseInterface($filter['interface']);
            $cmd .= ' | include "' . $iface['name'] . '"';
        } elseif(isset($filter['mac_address'])) {
            $mac = strtolower(Helper::formatMac($filter['mac_address']));
            $cmd .= ' | include "' . $mac . '"'; 
        } elseif(isset($filter['ip'])) {
            $cmd .= ' | include "' . $filter['ip'] . '"';
        } elseif(isset($filter['vlan_id'])) {
            $cmd .= ' | include "' . $filter['vlan_id'] . '"'; 
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];
        foreach($r as $line) {
            $m = [];
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,10})\s+(dhcp_sn|manual)\s+(\d{1,4})\s+((g|tg|epon|gpon|fe)\d{1,3}\/\d{1,3})$/i', trim($line), $m)) {
                if(isset($filter['vlan_id']) && $m[7] != $filter['vlan_id']) continue;
                $resp[] = [
                    'interface' => $this->parseInterface($m[8]),
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[7],
                    'ip' => $m[3],
                    'remaining' => (int) $m[5],
                    '_type' => strtoupper($m[6]),
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}

