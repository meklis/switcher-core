<?php


namespace SwitcherCore\Modules\Dlink\Snooping;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * DHCP snooping bindings via swIpMacBindMIB (enterprises.171.12.23), part of the
 * shared dlink-common-mgmt tree. Only present on the "managed" tier (DES-3200/30xx,
 * DGS-3000/3100/3120/3420) - confirmed absent on the web-smart "ME" line
 * (DGS-1100/1210-ME) by live probing across the full supported model list.
 *
 * In production a switch runs exactly one of two mutually exclusive DHCP
 * forwarding modes - plain DHCP Relay (swDHCPRelayMIB.1) or DHCP Local Relay
 * (swDHCPRelayMIB.4), never both. Neither mode keeps a client-binding table of
 * its own (verified against the vendor MIB - both are pure forwarding/config
 * trees), so swIpMacBindingDHCPSnoopTable stays the only source of MAC/IP/port
 * data regardless of which mode is active; the relay mode is surfaced per-entry
 * as `_relay_mode` since it's still useful context (e.g. a binding seen under
 * plain Relay reached this switch differently than one seen under Local Relay).
 */
class DefaultParser extends SwitchesPortAbstractModule
{
    protected function formate() {
        $mac = [];
        $lease = [];
        $port = [];
        $status = [];
        if (!$this->response['dhcpSnoop.mac']->error()) {
            foreach ($this->response['dhcpSnoop.mac']->fetchAll() as $r) {
                $mac[Helper::oid2IP($r->getOid())] = $r->getValue();
            }
        }
        if (!$this->response['dhcpSnoop.leaseTime']->error()) {
            foreach ($this->response['dhcpSnoop.leaseTime']->fetchAll() as $r) {
                $lease[Helper::oid2IP($r->getOid())] = $r->getValue();
            }
        }
        if (!$this->response['dhcpSnoop.port']->error()) {
            foreach ($this->response['dhcpSnoop.port']->fetchAll() as $r) {
                $port[Helper::oid2IP($r->getOid())] = $r->getValue();
            }
        }
        if (!$this->response['dhcpSnoop.status']->error()) {
            foreach ($this->response['dhcpSnoop.status']->fetchAll() as $r) {
                $status[Helper::oid2IP($r->getOid())] = $r->getValue();
            }
        }

        $relayMode = $this->relayMode();
        $indexes = $this->getIndexes();
        $entries = [];
        foreach ($status as $ip => $st) {
            // inactive(1) entries are stale/expired rows the agent hasn't swept yet
            if ((int)$st !== 2) continue;
            if (!isset($mac[$ip], $port[$ip])) continue;
            $portId = (string)(int)$port[$ip];
            if (!isset($indexes[$portId])) continue;

            $entries[] = [
                'interface' => $indexes[$portId],
                'mac_address' => $this->decodeMac($mac[$ip]),
                'vlan_id' => null,
                'ip' => $ip,
                'remaining' => isset($lease[$ip]) ? (int)$lease[$ip] : null,
                '_relay_mode' => $relayMode,
            ];
        }
        return $entries;
    }

    /**
     * @return string 'relay'|'local_relay'|'none' - which of the two mutually
     * exclusive forwarding modes is currently active on this switch.
     */
    private function relayMode() {
        $local = isset($this->response['dhcpRelay.localState']) && !$this->response['dhcpRelay.localState']->error()
            ? $this->response['dhcpRelay.localState']->fetchOne()
            : null;
        if ($local && (int)$local->getValue() === 1) {
            return 'local_relay';
        }
        $relay = isset($this->response['dhcpRelay.state']) && !$this->response['dhcpRelay.state']->error()
            ? $this->response['dhcpRelay.state']->fetchOne()
            : null;
        if ($relay && (int)$relay->getValue() === 1) {
            return 'relay';
        }
        return 'none';
    }

    /**
     * Net-SNMP (and PHP's ext-snmp, used here) prints an OCTET STRING as a
     * space-separated hex dump only when it contains non-printable bytes; a MAC
     * whose 6 raw bytes all happen to be printable ASCII comes back as plain text
     * instead (confirmed live on this device family's ARP table). Handle both.
     */
    private function decodeMac($raw) {
        $stripped = str_replace(' ', '', trim($raw));
        if (preg_match('/^[0-9A-Fa-f]{12}$/', $stripped)) {
            return Helper::formatMac($stripped);
        }
        $hex = '';
        for ($i = 0; $i < strlen($raw); $i++) {
            $hex .= str_pad(dechex(ord($raw[$i])), 2, '0', STR_PAD_LEFT);
        }
        return Helper::formatMac($hex);
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        $entries = $this->formate();
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            $entries = array_values(array_filter($entries, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            }));
        }
        if (!empty($filter['mac_address'])) {
            $mac = Helper::formatMac($filter['mac_address']);
            $entries = array_values(array_filter($entries, function ($e) use ($mac) {
                return $e['mac_address'] === $mac;
            }));
        }
        if (!empty($filter['ip'])) {
            $entries = array_values(array_filter($entries, function ($e) use ($filter) {
                return $e['ip'] === $filter['ip'];
            }));
        }
        return $entries;
    }

    /**
     * @param array $filter
     * @return $this
     * @throws \Exception
     */
    public function run($filter = []) {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dhcpSnoop.mac')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.leaseTime')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.port')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpSnoop.status')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpRelay.state')->getOid()),
            Oid::init($this->oids->getOidByName('dhcpRelay.localState')->getOid()),
        ]));
        return $this;
    }
}
