<?php


namespace SwitcherCore\Modules\General;

use InvalidArgumentException;
use SwitcherCore\Modules\AbstractModule;

/**
 * ARP ping for devices without native arp-ping (Juniper, ExtremeXOS, Dell, Arista).
 * Sends ICMP ping over console and then checks that ARP entry for this IP is present,
 * using the device own `arps` module. ARP entry is not cleared before ping, so `host`
 * may come from ARP cache - check `received` to be sure the host replied right now.
 */
abstract class ConsoleArpPing extends AbstractModule
{
    /**
     * Ping command in vendor syntax
     *
     * @param string $ip
     * @param int $count
     * @return string
     */
    abstract protected function getPingCommand($ip, $count);

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

    public function run($params = [])
    {
        if (!isset($params['ip']) || !$params['ip']) {
            throw new InvalidArgumentException("ip is required for this module");
        }
        $count = isset($params['count']) && (int)$params['count'] > 0 ? (int)$params['count'] : 1;

        $command = $this->getPingCommand($params['ip'], $count);
        $res = $this->getModule('console_command')->run(['command' => $command])->getPrettyFiltered();
        if (!$res['success']) {
            throw new \Exception("Error while running command {$command}. output = {$res['output']}");
        }
        $stat = $this->parsePingOutput($res['output']);

        // Проверяем ARP после пинга - ping обновит запись, если хост ответил на ARP
        $arpParams = ['ip' => $params['ip']];
        if (isset($params['vlan_id']) && $params['vlan_id']) {
            $arpParams['vlan_id'] = $params['vlan_id'];
        }
        $arp = null;
        foreach ($this->getModule('arps')->run($arpParams)->getPrettyFiltered() as $a) {
            if ($a['ip'] == $params['ip']) {
                $arp = $a;
                break;
            }
        }

        $this->response = [[
            'seq' => (string)($count - 1),
            'host' => $arp ? $arp['mac'] : null,
            'sent' => (string)$stat['sent'],
            'received' => (string)$stat['received'],
            'packet-loss' => (string)$stat['loss'],
            'min-rtt' => $stat['min'] !== null ? "{$stat['min']}ms" : null,
            'avg-rtt' => $stat['avg'] !== null ? "{$stat['avg']}ms" : null,
            'max-rtt' => $stat['max'] !== null ? "{$stat['max']}ms" : null,
            '_arp' => $arp,
            '_ping_output' => $res['output'],
        ]];
        return $this;
    }

    /**
     * Parse ping statistics. Supports formats:
     *  "3 packets transmitted, 3 packets received, 0% packet loss" (JunOS, EXOS)
     *  "3 packets transmitted, 3 received, 0% packet loss" (Linux based - EOS, OS10)
     *  "Success rate is 100.0 percent (3/3), round-trip min/avg/max = 0/0/0 (ms)" (Dell OS9)
     *
     * @param string $output
     * @return array
     */
    protected function parsePingOutput($output)
    {
        $stat = ['sent' => 0, 'received' => 0, 'loss' => 100, 'min' => null, 'avg' => null, 'max' => null];
        if (preg_match('/(\d+)\s+packets\s+transmitted,\s+(\d+)\s+(?:packets\s+)?received/i', $output, $m)) {
            $stat['sent'] = (int)$m[1];
            $stat['received'] = (int)$m[2];
        } elseif (preg_match('/Success\s+rate\s+is\s+[\d.]+\s+percent\s+\((\d+)\/(\d+)\)/i', $output, $m)) {
            $stat['received'] = (int)$m[1];
            $stat['sent'] = (int)$m[2];
        }
        if ($stat['sent'] > 0) {
            $stat['loss'] = (int)round(($stat['sent'] - $stat['received']) / $stat['sent'] * 100);
        }
        if (preg_match('/min\/avg\/max(?:\/\S+)?\s*=\s*([\d.]+)\/([\d.]+)\/([\d.]+)/i', $output, $m)) {
            $stat['min'] = (float)$m[1];
            $stat['avg'] = (float)$m[2];
            $stat['max'] = (float)$m[3];
        }
        return $stat;
    }
}
