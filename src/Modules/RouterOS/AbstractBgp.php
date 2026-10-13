<?php

namespace SwitcherCore\Modules\RouterOS;

use Exception;

/**
 * Common BGP logic for RouterOS v6 (/routing/bgp/peer, /routing/bgp/instance)
 * and RouterOS v7 (/routing/bgp/connection, /routing/bgp/session, /routing/bgp/instance since 7.20).
 * Output format is the same as for SNMP based bgp_info/bgp_peers modules (Juniper, ExtremeXOS).
 */
abstract class AbstractBgp extends ExecCommand
{
    protected $response = [];

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

    /**
     * Returns command output or null when command is not supported by this RouterOS version
     */
    protected function execOptional($comm)
    {
        try {
            return $this->execComm($comm);
        } catch (Exception $e) {
            if (stripos($e->getMessage(), 'no such command') !== false) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * @return array list of peers in bgp_peers format with optional '_name',
     *               internal keys (instance, local AS/ID) are prefixed with '__'
     */
    protected function loadPeers()
    {
        $peers = $this->execOptional('/routing/bgp/peer/print');
        if ($peers !== null) {
            return array_map([$this, 'formatV6Peer'], $peers);
        }
        return $this->loadV7Peers();
    }

    protected function formatV6Peer($peer)
    {
        $disabled = ($peer['disabled'] ?? 'false') === 'true';
        $established = ($peer['established'] ?? 'false') === 'true';
        $remoteAddr = $this->cleanAddress($peer['remote-address'] ?? null);
        return [
            'identifier' => $peer['remote-id'] ?? null,
            'state' => $disabled ? 'idle' : ($established ? 'established' : ($peer['state'] ?? 'idle')),
            'admin_status' => $disabled ? 'disabled' : 'enabled',
            'negotiated_version' => $disabled ? '0' : '4',
            'local_addr' => $this->cleanAddress($peer['local-address'] ?? null),
            'local_port' => null,
            'remote_addr' => $remoteAddr,
            'remote_port' => null,
            'remote_as' => $peer['remote-as'] ?? null,
            'last_error' => null, // not available in RouterOS v6
            'established_time' => $established ? $this->durationToSeconds($peer['uptime'] ?? null) : null,
            'ip_version' => $this->ipVersion($remoteAddr),
            '_name' => $peer['name'] ?? null,
            '__instance' => $peer['instance'] ?? null,
        ];
    }

    protected function loadV7Peers()
    {
        $connections = $this->execOptional('/routing/bgp/connection/print') ?: [];
        $sessions = $this->execOptional('/routing/bgp/session/print') ?: [];

        // Session name is "<connection name>-<N>", one connection can have several sessions (listen ranges)
        $sessionsByConnection = [];
        $orphanSessions = [];
        $connectionNames = array_flip(array_map(function ($c) {
            return $c['name'] ?? '';
        }, $connections));
        foreach ($sessions as $session) {
            $connName = preg_replace('/-\d+$/', '', $session['name'] ?? '');
            if (isset($connectionNames[$connName])) {
                $sessionsByConnection[$connName][] = $session;
            } else {
                $orphanSessions[] = $session;
            }
        }

        $result = [];
        foreach ($connections as $connection) {
            $name = $connection['name'] ?? '';
            if (!empty($sessionsByConnection[$name])) {
                foreach ($sessionsByConnection[$name] as $session) {
                    $result[] = $this->formatV7Peer($connection, $session);
                }
            } else {
                $result[] = $this->formatV7Peer($connection, null);
            }
        }
        foreach ($orphanSessions as $session) {
            $result[] = $this->formatV7Peer(null, $session);
        }
        return $result;
    }

    protected function formatV7Peer($connection, $session)
    {
        $disabled = $connection && ($connection['disabled'] ?? 'false') === 'true';
        $established = $session && ($session['established'] ?? 'false') === 'true';

        $remoteAs = $session['remote.as'] ?? null;
        if (!$remoteAs || $remoteAs === '0') {
            $remoteAs = $connection['remote.as'] ?? $remoteAs;
        }
        $remoteAddr = $this->cleanAddress($session['remote.address'] ?? ($connection['remote.address'] ?? null));
        $localAddr = $this->cleanAddress($session['local.address'] ?? ($connection['local.address'] ?? null));

        return [
            'identifier' => $session['remote.id'] ?? null,
            'state' => $established ? 'established' : 'idle', // RouterOS v7 does not expose FSM state of non established sessions
            'admin_status' => $disabled ? 'disabled' : 'enabled',
            'negotiated_version' => $disabled ? '0' : '4',
            'local_addr' => $localAddr,
            'local_port' => null,
            'remote_addr' => $remoteAddr,
            'remote_port' => null,
            'remote_as' => $remoteAs,
            'last_error' => $session ? $this->notificationToError($session['output.last-notification'] ?? null) : null,
            'established_time' => $established ? $this->durationToSeconds($session['uptime'] ?? null) : null,
            'ip_version' => $this->ipVersion($remoteAddr),
            '_name' => $connection['name'] ?? preg_replace('/-\d+$/', '', $session['name'] ?? ''),
            '__instance' => $session['instance'] ?? ($connection['instance'] ?? null),
            '__local_as' => $session['local.as'] ?? ($connection['as'] ?? null),
            '__local_id' => $session['local.id'] ?? ($connection['router-id'] ?? null),
        ];
    }

    /**
     * Raw BGP NOTIFICATION message (16 bytes marker, 2 bytes length, type=03, code, subcode, data)
     * converted to "<code> <subcode>" as in BGP4-MIB bgpPeerLastError. No notification - "00 00"
     */
    protected function notificationToError($hex)
    {
        $hex = strtoupper(preg_replace('/[^0-9a-fA-F]/', '', (string)$hex));
        if (strlen($hex) < 42 || substr($hex, 36, 2) !== '03') {
            return '00 00';
        }
        return substr($hex, 38, 2) . ' ' . substr($hex, 40, 2);
    }

    /**
     * RouterOS duration (for example 6w1d4h33m33s880ms) to seconds
     */
    protected function durationToSeconds($duration)
    {
        if ($duration === null || $duration === '') {
            return null;
        }
        if (preg_match('/^(\d+):(\d+):(\d+)$/', $duration, $m)) {
            return (string)($m[1] * 3600 + $m[2] * 60 + $m[3]);
        }
        $multipliers = ['w' => 604800, 'd' => 86400, 'h' => 3600, 'm' => 60, 's' => 1, 'ms' => 0];
        preg_match_all('/(\d+)(ms|w|d|h|m|s)/', $duration, $matches, PREG_SET_ORDER);
        $seconds = 0;
        foreach ($matches as $match) {
            $seconds += $match[1] * $multipliers[$match[2]];
        }
        return (string)$seconds;
    }

    /**
     * Removes interface scope (fe80::1%ether1) and prefix length (2a03::1/128)
     */
    protected function cleanAddress($address)
    {
        if ($address === null || $address === '') {
            return null;
        }
        return preg_replace('/[%\/].*$/', '', $address);
    }

    protected function ipVersion($address)
    {
        if (!$address) {
            return null;
        }
        return strpos($address, ':') !== false ? 'ipv6' : 'ipv4';
    }
}
