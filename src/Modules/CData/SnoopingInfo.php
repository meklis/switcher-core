<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\CData\CDataAbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * DHCP snooping bindings via nscrtvPonTreeExt.dhcpManagementObjects
 * (dhcpSnoopingBindTable), confirmed live and cross-checked against the
 * console `show dhcp-snooping bind-table all` output (MAC/IP/VLAN/port/
 * lease all matched exactly). SNMP is missing the ONU id and lease/remaining
 * columns the MIB declares (present in the schema, "No Such Object" on real
 * firmware) and dhcpSnBindPortId only ever resolves to the physical port's
 * ifIndex, never a specific ONU - so a request scoped to a specific ONU
 * falls back to the console table, which the base run() logic already
 * handles correctly (and is the only source of onu_id/remaining anyway).
 */
class SnoopingInfo extends CDataAbstractModule {
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
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] === 'ONU') {
                return $this->runViaConsole($filter);
            }
        }
        try {
            return $this->runViaSnmp();
        } catch (Exception $e) {
            // Tree not implemented on this firmware/model - fall back rather
            // than fail the whole call.
            return $this->runViaConsole($filter);
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function runViaSnmp() {
        // Indexed by the raw IP octets - not confirmed to always come back
        // in strict lexicographic order, same reasoning as BDcom.
        $this->snmp->setOidIncreasingCheck(false);
        $data = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dhcpSnoop.mac')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.port')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.type')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.rowStatus')->getOid()),
        ]));
        $this->snmp->setOidIncreasingCheck(true);

        $byIndex = [];
        $collect = function ($name, $key) use (&$byIndex, $data) {
            if (!isset($data[$name]) || $data[$name]->error()) return;
            foreach ($data[$name]->fetchAll() as $r) {
                // Index is [Ip1, Ip2, Ip3, Ip4, Vlan] - 5 trailing components.
                $index = Helper::getIndexByOid($r->getOid(), 4) . '.'
                    . Helper::getIndexByOid($r->getOid(), 3) . '.'
                    . Helper::getIndexByOid($r->getOid(), 2) . '.'
                    . Helper::getIndexByOid($r->getOid(), 1) . '.'
                    . Helper::getIndexByOid($r->getOid());
                $byIndex[$index][$key] = $r;
            }
        };
        $collect('dhcpSnoop.mac', 'mac');
        $collect('dhcpSnoop.port', 'port');
        $collect('dhcpSnoop.type', 'type');
        $collect('dhcpSnoop.rowStatus', 'status');

        $resp = [];
        foreach ($byIndex as $index => $row) {
            if (!isset($row['mac'], $row['port'], $row['status'])) continue;
            // RowStatus (RFC 2579): only active(1) rows are live bindings.
            if ((int) $row['status']->getValue() !== 1) continue;
            // dhcpSnBindPortId packs the ifIndex into the third byte
            // (portId >> 8) & 0xFF; a 0 here (seen for the OLT's own local/
            // management IP, shown as port "cpu" on console) isn't a real
            // client-facing port.
            $ifIndex = ((int) $row['port']->getValue() >> 8) & 0xFF;
            if ($ifIndex === 0) continue;
            try {
                $interface = $this->parseInterface($ifIndex);
            } catch (\Exception $e) {
                continue;
            }
            $parts = explode('.', $index);
            $resp[] = [
                'interface' => $interface,
                'mac_address' => $row['mac']->getHexValue(),
                'vlan_id' => (int) $parts[4],
                'ip' => "{$parts[0]}.{$parts[1]}.{$parts[2]}.{$parts[3]}",
                'remaining' => null,
                '_type' => isset($row['type']) ? $row['type']->getParsedValue() : null,
            ];
        }
        $this->response = $resp;
        return $this;
    }

    /**
     * The CLI on these devices (confirmed live) doesn't recognize the old
     * 'show dhcp security-table' command at all ("Unknown command") - it's a
     * newer vtysh-style dialect using 'show dhcp-snooping bind-table'
     * instead, which only supports scoping by IP or VLAN server-side (no
     * 'port'/'mac' sub-filter), and its Port column never carries an ONU
     * suffix either - so per-ONU precision isn't available from this
     * console any more than it is from SNMP. Kept as the fallback anyway
     * for parity with other C-Data variants and in case some port/mac
     * request needs it client-side filtered.
     *
     * @param array $filter
     * @return $this
     * @throws Exception
     */
    protected function runViaConsole($filter = []) {
        $cmd = 'show dhcp-snooping bind-table';
        if (isset($filter['ip'])) {
            $cmd .= ' ' . $filter['ip'];
        } elseif (isset($filter['vlan_id'])) {
            $cmd .= ' vlan ' . $filter['vlan_id'];
        } else {
            $cmd .= ' all';
        }
        $r = $this->getModule('console_command')->run(['command' => $cmd])->getPretty();
        $r = explode("\n", $r['output']);
        $resp = [];

        foreach($r as $line) {
            $m = [];
            if(preg_match('/^([0-9a-f]{2}(?::[0-9a-f]{2}){5})\s+((?:\d{1,3}\.){3}\d{1,3})\s+(\d{1,4})\s+(\S+)\s+(\d{1,10}|-)\s+(Dynamic|Static)\s+(Valid|Invalid)\s*$/i', trim($line), $m)) {
                if(strtoupper($m[7]) === 'INVALID') continue;
                try {
                    $interface = $this->parseInterface($m[4]);
                } catch (\Exception $e) {
                    continue; // "cpu" and other non-port pseudo-locations
                }
                $resp[] = [
                    'interface' => $interface,
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => (int) $m[3],
                    'ip' => $m[2],
                    'remaining' => is_numeric($m[5]) ? (int) $m[5] : null,
                    '_type' => strtoupper($m[6]),
                ];
            }
        }
        $this->response = $resp;
        return $this;
    }
}
