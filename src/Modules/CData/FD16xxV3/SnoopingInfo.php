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
            // Device's "port" filter only accepts the physical F/S/P (e.g. "pon 0/0/5"),
            // not a specific ONU on it ("Incorrect F/S/P parameters" otherwise) - narrowing
            // down to one ONU happens client-side in getPrettyFiltered().
            $if_name = preg_replace('/:\d+$/', '', $if_name);
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
        $fdbByPort = [];

        foreach($r as $line) {
            $m = [];
            // Колонка с номером ONU есть не на всех прошивках/моделях (например, её нет
            // на FD1608SN-R2-DAP V3.1.37) - без этого условия строка вообще не матчилась
            // и snooping_info возвращал пустой список на таких устройствах.
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,4})\s+((ge|lag|xge|epon|gpon|fe)\s\d{1,3}\/\d{1,3}\/\d{1,3})(?:\s+(\d{1,4}))?\s+(\d{1,10})\s+(dynamic|static)\s+(valid|invalid)$/i', trim($line), $m)) {
                if(strtoupper($m[11]) === 'INVALID') continue;
                $mac = Helper::formatMac($m[1]);
                if ($m[8] !== '') {
                    $interface = $this->parseInterface($m[6] . ':' . $m[8]);
                    $onuId = (int) $m[8];
                } else {
                    // Номера ONU нет в самой строке - определяем его по FDB порта (кэшируем per port,
                    // чтобы не дергать консоль по разу на каждую строку одного и того же порта).
                    $portIface = $this->parseInterface($m[6]);
                    if (!array_key_exists($portIface['id'], $fdbByPort)) {
                        $fdbByPort[$portIface['id']] = $this->getModule('fdb')->run(['interface' => $portIface['id']])->getPretty();
                    }
                    $fdbRow = null;
                    foreach ($fdbByPort[$portIface['id']] as $row) {
                        if ($row['mac_address'] == $mac) {
                            $fdbRow = $row;
                            break;
                        }
                    }
                    $interface = $fdbRow ? $fdbRow['interface'] : $portIface;
                    $onuId = $fdbRow ? ($fdbRow['interface']['_onu'] ?? null) : null;
                }
                $resp[] = [
                    'interface' => $interface,
                    'mac_address' => $mac,
                    'vlan_id' => (int) $m[5],
                    'ip' => $m[3],
                    'remaining' => (int) $m[9],
                    '_type' => strtoupper($m[10]),
                    '_onu_id' => $onuId,
                    //'_status' => strtoupper($m[11]),
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}

