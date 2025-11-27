<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SwitcherCore\Modules\CData\FD16xxV3\CDataAbstractModuleFD16xxV3;
use SwitcherCore\Modules\Helper;

class SnoopingInfo extends CDataAbstractModuleFD16xxV3 {
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
        $cmd = 'show dhcp security-table';
        if(isset($filter['interface'])) {
            $iface = $this->parseInterface($filter['interface']);
            $if_name = $iface['name'];
            if(strpos($if_name, 'gpon') !== false) $if_name = str_replace('gpon', 'pon', $if_name);
            $cmd .= ' port ' . $if_name;
        } elseif(isset($filter['mac_address'])) {
            $mac = Helper::formatMac($filter['mac_address']);
            $cmd .= ' mac ' . $mac; 
        } elseif(isset($filter['ip'])) {
            $cmd .= ' ' . $filter['ip'];
        } elseif(isset($filter['vlan_id'])) {
            $cmd .= ' vlan ' . $filter['vlan_id']; 
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];
        
        foreach($r as $line) {
            $m = [];
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,4})\s+((ge|lag|xge|epon|gpon|fe)\s\d{1,3}\/\d{1,3}\/\d{1,3})\s+(\d{1,10})\s+(dynamic|static)\s+(valid|invalid)$/i', trim($line), $m)) {
                if(strtoupper($m[10]) === 'INVALID') continue;
                $resp[] = [
                    'interface' => $this->parseInterface($m[6]),
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[5],
                    'ip' => $m[3],
                    'remaining' => (int) $m[8],
                    '_type' => strtoupper($m[9]),
                    //'_status' => strtoupper($m[10]),
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}

