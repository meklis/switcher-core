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
                return $e['interface']['id'] == $interface['id'] || $e['interface']['parent'] == $interface['id'];
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
        // Server-side "| include" filtering is unreliable on this firmware
        // (it fails to match IP addresses and bare port names, e.g. "epon0/5"),
        // so the full table is always fetched and filtering is done client-side
        // in getPrettyFiltered().
        $cmd = 'show ip dhcp-relay snooping binding all';
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];
        foreach($r as $line) {
            $m = [];
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,10})\s+(dhcp_sn|manual)\s+(\d{1,4})\s+(\d{1,4})\s+((g|tg|epon|gpon|fe)\d{1,3}\/\d{1,3}(:\d{1,4})?)$/i', trim($line), $m)) {
                $resp[] = [
                    'interface' => $this->parseInterface($m[9]),
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[7],
                    'ip' => $m[3],
                    'remaining' => (int) $m[5],
                    '_type' => strtoupper($m[6]),
                    '_cvlan' => (int) $m[8],
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}

