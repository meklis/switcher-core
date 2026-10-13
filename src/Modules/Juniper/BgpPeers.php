<?php

namespace SwitcherCore\Modules\Juniper;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * BGP peers from BGP4-V2-MIB-JUNIPER (IPv4 + IPv6).
 * Falls back to the standard BGP4-MIB (IPv4 only) when jnxBgpM2 is not available.
 */
class BgpPeers extends AbstractModule
{
    const ADDR_TYPE_IPV4 = 1;
    const ADDR_TYPE_IPV6 = 2;

    protected $useJnx = false;

    public function run($params = [])
    {
        $this->useJnx = false;
        try {
            $this->response = $this->formatResponse($this->snmp->walk($this->oids->getOidsByRegex('^jnx\.bgp\.peer\..*')));
            $state = $this->getResponseByName('jnx.bgp.peer.state');
            if (!$state->error() && $state->fetchAll()) {
                $this->useJnx = true;
                return $this;
            }
        } catch (\Exception $e) {
        }

        $this->response = $this->formatResponse($this->snmp->walk($this->oids->getOidsByRegex('^bgp\.peer\..*')));
        foreach ($this->response as $data) {
            if($data->error()) {
                throw new \SNMPException($data->error());
            }
        }

        return $this;
    }

    public function getPretty()
    {
        return $this->useJnx ? $this->getPrettyJnx() : $this->getPrettyStandard();
    }

    protected function getPrettyStandard()
    {
        $bgpPeers = [];
        foreach ($this->response as $key => $data) {
            $name = str_replace("bgp.peer.", "", $key);
            foreach ($data->fetchAll() as $d) {
                $idx =
                    Helper::getIndexByOid($d->getOid(), 3) . "." .
                    Helper::getIndexByOid($d->getOid(), 2) . "." .
                    Helper::getIndexByOid($d->getOid(), 1) . "." .
                    Helper::getIndexByOid($d->getOid()) ;
                $bgpPeers[$idx][$name] = $d->getParsedValue();
            }
        }
        foreach ($bgpPeers as &$peer) {
            $peer['ip_version'] = 'ipv4';
        }
        return array_values($bgpPeers);
    }

    protected function getPrettyJnx()
    {
        $peerOidPrefixes = [];
        foreach ($this->response as $key => $data) {
            $peerOidPrefixes[$key] = $this->oids->getOidByName($key)->getOid();
        }

        $bgpPeers = [];
        foreach ($this->response as $key => $data) {
            if ($data->error()) {
                continue;
            }
            $name = str_replace("jnx.bgp.peer.", "", $key);
            $prefix = trim($peerOidPrefixes[$key], '.') . '.';
            foreach ($data->fetchAll() as $d) {
                $idx = substr(trim($d->getOid(), '.'), strlen($prefix));
                if (!isset($bgpPeers[$idx])) {
                    $bgpPeers[$idx] = $this->parseJnxIndex($idx);
                }
                switch ($name) {
                    case 'identifier':
                        $bgpPeers[$idx][$name] = $this->hexToIp($d->getHexValue());
                        break;
                    case 'last_error':
                        $bgpPeers[$idx][$name] = implode(' ', str_split($this->normalizeHex($d->getHexValue()), 2));
                        break;
                    default:
                        $bgpPeers[$idx][$name] = $d->getParsedValue();
                }
            }
        }

        $fields = ['identifier', 'state', 'admin_status', 'negotiated_version', 'local_addr', 'local_port',
            'remote_addr', 'remote_port', 'remote_as', 'last_error', 'established_time', 'ip_version'];
        $result = [];
        foreach ($bgpPeers as $peer) {
            if (!isset($peer['state'])) {
                continue;
            }
            // Junos does not fill jnxBgpM2PeerNegotiatedVersion, BGP4-MIB reports configured version for enabled peers
            if (empty($peer['negotiated_version']) && ($peer['admin_status'] ?? null) === 'enabled') {
                $peer['negotiated_version'] = $peer['configured_version'] ?? null;
            }
            $peer['ip_version'] = strpos((string)($peer['remote_addr'] ?? ''), ':') !== false ? 'ipv6' : 'ipv4';
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = $peer[$field] ?? null;
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * <routing instance>.<local addr type>.<local addr bytes>.<remote addr type>.<remote addr bytes>
     */
    protected function parseJnxIndex($idx)
    {
        $parts = array_map('intval', explode('.', $idx));
        $pos = 1;
        $local = $this->readAddr($parts, $pos);
        $remote = $this->readAddr($parts, $pos);
        return [
            'local_addr' => $local,
            'remote_addr' => $remote,
        ];
    }

    protected function readAddr(array $parts, &$pos)
    {
        $type = $parts[$pos] ?? null;
        $len = $type === self::ADDR_TYPE_IPV6 ? 16 : 4;
        $bytes = array_slice($parts, $pos + 1, $len);
        $pos += 1 + $len;
        if (count($bytes) !== $len) {
            return null;
        }
        return inet_ntop(pack('C*', ...$bytes));
    }

    protected function normalizeHex($hex)
    {
        return strtoupper(preg_replace('/[^0-9a-fA-F]/', '', (string)$hex));
    }

    protected function hexToIp($hex)
    {
        $hex = $this->normalizeHex($hex);
        if (strlen($hex) !== 8 && strlen($hex) !== 32) {
            return null;
        }
        return inet_ntop(hex2bin($hex));
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}
