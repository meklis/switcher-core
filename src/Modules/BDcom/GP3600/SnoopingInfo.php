<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SwitcherCore\Modules\BDcom\GP3600\BDcomAbstractModule;
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
                // Таблица снупинга привязана к PON-порту, а не к ONU
                return $e['interface']['id'] == $interface['id']
                    || $e['interface']['parent'] == $interface['id']
                    || $e['interface']['id'] == $interface['parent'];
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
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            $mac = $interface['type'] === 'ONU' ? $this->getOnuMac($filter['interface']) : null;
            $portName = $interface['type'] === 'ONU' ? preg_replace('/:\d+$/', '', $interface['name']) : $interface['name'];
            // Грубый серверный пре-фильтр (| include ищет по подстроке), точная фильтрация - в getPrettyFiltered()
            $cmd .= ' | include ' . ($mac ?: $portName);
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];
        $fdbByPort = [];
        foreach($r as $line) {
            $m = [];
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,10})\s+(dhcp_sn|manual)\s+(\d{1,4})\s+((g|tg|epon|gpon|fe)\d{1,3}\/\d{1,3})$/i', trim($line), $m)) {
                // Эта таблица не показывает номер ONU вообще - только родительский PON-порт.
                // Определяем ONU по FDB порта (кэш per port), как в BDcom\SnoopingInfo.
                $interface = $this->parseInterface($m[8]);
                $mac = Helper::formatMac($m[1]);
                if (!array_key_exists($interface['id'], $fdbByPort)) {
                    $fdbByPort[$interface['id']] = $this->getModule('fdb')->run(['interface' => $interface['id']])->getPretty();
                }
                foreach ($fdbByPort[$interface['id']] as $fdbRow) {
                    if (Helper::formatMac($fdbRow['mac_address']) === $mac) {
                        $interface = $fdbRow['interface'];
                        break;
                    }
                }
                $resp[] = [
                    'interface' => $interface,
                    'mac_address' => $mac,
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

    private function getOnuMac($ifaceIdent) {
        // MAC абонента из FDB по этому интерфейсу - точнее и быстрее, чем grep по всему порту
        $fdb = $this->getModule('fdb')->run(['interface' => $ifaceIdent])->getPretty();
        return $fdb ? strtolower($fdb[0]['mac_address']) : null;
    }
}

