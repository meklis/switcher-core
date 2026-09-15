<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\BDcom\BDcomAbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * DHCP snooping bindings via NMS-DHCP-SNOOPING-MIB (nmslocal.233), confirmed
 * live to match the console table field-for-field. `nmsBindingsInterface`
 * is a standard ifIndex, so it's resolved the same way for a physical port
 * or an ONU sub-interface - both get their own ifIndex in this vendor's
 * ifTable. The one thing SNMP can't be trusted for is a request scoped to a
 * specific ONU: it's unconfirmed whether every firmware binds the entry to
 * the ONU's own ifIndex rather than its parent port's, so an ONU-level
 * interface filter falls back to the console table, which is unambiguous.
 */
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
                // На части прошивок таблица привязана к PON-порту, а не к ONU
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
        $interface = null;
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] === 'ONU') {
                return $this->runViaConsole($interface);
            }
        }
        try {
            return $this->runViaSnmp();
        } catch (Exception $e) {
            // Tree not implemented on this firmware/model - fall back rather
            // than fail the whole call.
            return $this->runViaConsole($interface);
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function runViaSnmp() {
        // This table is indexed by the IP address encoded as a single
        // 32-bit sub-identifier; at least some firmware doesn't return rows
        // in strict lexicographic order for it, which PHP's native SNMP
        // walk (unlike net-snmp's own snmpwalk/-Cc) treats as fatal.
        $this->snmp->setOidIncreasingCheck(false);
        $data = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dhcpSnoop.ip')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.mac')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.vlan')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.interface')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.leaseTime')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.type')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.rowStatus')->getOid()),
        ]));
        $this->snmp->setOidIncreasingCheck(true);

        $byIndex = [];
        // The MIB documents this table as indexed by IpAddress alone, but at
        // least one firmware (confirmed live) actually indexes it by
        // [IpAddress, Vlan, Interface] instead - so the index shape isn't
        // assumed to have a fixed number of components; whatever's left
        // after stripping the requested column's own OID prefix is used
        // as-is for grouping, which works for either shape.
        $collect = function ($name, $key) use (&$byIndex, $data) {
            if (!isset($data[$name]) || $data[$name]->error()) return;
            $prefix = $this->oids->getOidByName($name)->getOid() . '.';
            foreach ($data[$name]->fetchAll() as $r) {
                $index = substr($r->getOid(), strlen($prefix));
                $byIndex[$index][$key] = $r;
            }
        };
        $collect('dhcpSnoop.ip', 'ip');
        $collect('dhcpSnoop.mac', 'mac');
        $collect('dhcpSnoop.vlan', 'vlan');
        $collect('dhcpSnoop.interface', 'iface');
        $collect('dhcpSnoop.leaseTime', 'lease');
        $collect('dhcpSnoop.type', 'type');
        $collect('dhcpSnoop.rowStatus', 'status');

        $resp = [];
        $fdbByPort = [];
        foreach ($byIndex as $row) {
            if (!isset($row['ip'], $row['mac'], $row['vlan'], $row['iface'], $row['status'])) continue;
            // RowStatus (RFC 2579): only active(1) rows are live bindings.
            if ((int) $row['status']->getValue() !== 1) continue;
            try {
                $interface = $this->parseInterface((int) $row['iface']->getValue(), 'xid');
            } catch (\Exception $e) {
                continue;
            }
            $mac = $row['mac']->getHexValue();
            if ($interface['type'] === 'PON') {
                // На части прошивок nmsBindingsInterface указывает на родительский PON-порт,
                // а не на конкретную ONU (см. докблок класса) - определяем ONU по FDB порта,
                // кэшируя per port, чтобы не дёргать устройство на каждую строку.
                if (!array_key_exists($interface['id'], $fdbByPort)) {
                    $fdbByPort[$interface['id']] = $this->getModule('fdb')->run(['interface' => $interface['id']])->getPretty();
                }
                foreach ($fdbByPort[$interface['id']] as $fdbRow) {
                    if (Helper::formatMac($fdbRow['mac_address']) === Helper::formatMac($mac)) {
                        $interface = $fdbRow['interface'];
                        break;
                    }
                }
            }
            $resp[] = [
                'interface' => $interface,
                'mac_address' => $mac,
                'vlan_id' => (int) $row['vlan']->getValue(),
                'ip' => $row['ip']->getValue(),
                'remaining' => isset($row['lease']) ? (int) $row['lease']->getValue() : null,
                '_type' => isset($row['type']) ? $row['type']->getParsedValue() : null,
            ];
        }
        $this->response = $resp;
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function runViaConsole($interface = null) {
        $cmd = 'show ip dhcp-relay snooping binding all';
        if ($interface) {
            $mac = $interface['type'] === 'ONU' ? $this->getOnuMac($interface) : null;
            $portName = $interface['type'] === 'ONU' ? preg_replace('/:\d+$/', '', $interface['name']) : $interface['name'];
            // Грубый серверный пре-фильтр (| include ищет по подстроке), точная фильтрация - в getPrettyFiltered()
            $cmd .= ' | include ' . ($mac ?: $portName);
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];
        foreach($r as $line) {
            $m = [];
            // Some firmware/model variants (e.g. P3608B) print only VLAN,
            // others (e.g. observed on other BDcom switches) also print a
            // second CVLAN column - keep the second number optional instead
            // of required, so both formats match.
            if(preg_match('/^(([0-9a-f]{2}:?){6})\s+((\d{1,3}\.?){4})\s+(\d{1,10})\s+(dhcp_sn|manual)\s+(\d{1,4})(?:\s+(\d{1,4}))?\s+((g|tg|epon|gpon|fe)\d{1,3}\/\d{1,3}(:\d{1,4})?)$/i', trim($line), $m)) {
                $resp[] = [
                    'interface' => $this->parseInterface($m[9]),
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[7],
                    'ip' => $m[3],
                    'remaining' => (int) $m[5],
                    '_type' => strtoupper($m[6]),
                    '_cvlan' => !empty($m[8]) ? (int) $m[8] : null,
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }

    private function getOnuMac($interface) {
        // MAC абонента из FDB по этому интерфейсу - точнее и быстрее, чем grep по всему порту
        $fdb = $this->getModule('fdb')->run(['interface' => $interface['id']])->getPretty();
        return $fdb ? strtolower($fdb[0]['mac_address']) : null;
    }
}
