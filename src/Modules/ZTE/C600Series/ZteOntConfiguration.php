<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

use SebastianBergmann\Environment\Console;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class ZteOntConfiguration extends ModuleAbstract
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    public function run($params = [])
    {
        if (!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $iface = $this->parseInterface($params['interface']);

        $this->console->exec("configure terminal");
        // 1) vlan port vport...
        $out1 = $this->console->exec("show vlan port vport-{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}.{$iface['_onu']}:1", true, '[\#\$]');
        $vportVlans = $this->parseShowVlanPort($out1);

        // 2) interface vport... + show this
        $this->console->exec("interface vport-{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}.{$iface['_onu']}:1", true, '[\#\$]');
        $out2 = $this->console->exec("show this", true, '[\#\$]');
        $this->console->exec("exit", true, '[\#\$]');
        $vportThis = $this->parseVportShowThis($out2);

        // 3) pon-onu-mng ... + show this
        $this->console->exec("pon-onu-mng {$iface['name']}", true, '[\#\$]');
        $out3 = $this->console->exec("show this", true, '[\#\$]');
        $this->console->exec("exit", true, '[\#\$]');
        $onuThis = $this->parseOnuShowThis($out3);

        $this->response = [
            'vport' => array_merge($vportVlans, $vportThis),
            'onu' => $onuThis,
            'interface' => $iface,
        ];
        return $this;
    }


    /**
     * Собирает всё и возвращает ассоциативный массив.
     *
     * @return array{
     *   vport: array{
     *     mode:?string,pvid:?int,
     *     untagged:int[],tagged:int[]
     *   },
     *   vport_show_this: array{
     *     service_ports: array<int,array{service_port:int,user_vlan:?int,vlan:?int}>,
     *     traffic_policies: array<int,array{name:string,direction:string}>
     *   },
     *   onu: array{
     *     gemport_vlans: array<int,int[]>,
     *     eth_vlans: array<string,int[]>,
     *     eth_mode: array<string,string>
     *   }
     * }
     */
    public function getPretty()
    {
        return $this->response;
    }

    /**
     * Собирает всё и возвращает ассоциативный массив.
     *
     * @return array{
     *   vport: array{
     *     mode:?string,pvid:?int,
     *     untagged:int[],tagged:int[]
     *   },
     *   vport_show_this: array{
     *     service_ports: array<int,array{service_port:int,user_vlan:?int,vlan:?int}>,
     *     traffic_policies: array<int,array{name:string,direction:string}>
     *   },
     *   onu: array{
     *     gemport_vlans: array<int,int[]>,
     *     eth_vlans: array<string,int[]>,
     *     eth_mode: array<string,string>
     *   }
     * }
     */
    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }


    // ---------------- PARSERS ----------------

    private function parseShowVlanPort(string $out): array
    {
        $mode = null;
        $pvid = null;

        // "hybrid    --   --    0x8100 ..."
        if (preg_match('/^\s*([a-zA-Z]+)\s+(\d+|--)\s+/m', $out, $m)) {
            $mode = strtolower($m[1]);
            $pvid = ($m[2] === '--') ? null : (int)$m[2];
        }

        return [
            'mode' => $mode,
            'pvid' => $pvid,
            'untagged' => $this->parseVlanSection($out, 'UntaggedVlan'),
            'tagged' => $this->parseVlanSection($out, 'TaggedVlan'),
        ];
    }

    private function parseVportShowThis(string $out): array
    {
        $servicePorts = [];
        $traffic = [];

        // service-port 1 user-vlan 2543 vlan 2543
        if (preg_match_all('/^\s*service-port\s+(\d+)(?:\s+user-vlan\s+(\d+))?(?:\s+vlan\s+(\d+))?\s*$/mi', $out, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $servicePorts[] = [
                    'service_port' => (int)$m[1],
                    'user_vlan' => ($m[2] ?? '') !== '' ? (int)$m[2] : null,
                    'vlan' => ($m[3] ?? '') !== '' ? (int)$m[3] : null,
                ];
            }
        }

        // qos traffic-policy 100M direction egress|ingress
        if (preg_match_all('/^\s*qos\s+traffic-policy\s+(\S+)\s+direction\s+(egress|ingress)\s*$/mi', $out, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $traffic[] = [
                    'name' => (string)$m[1],
                    'direction' => strtolower($m[2]),
                ];
            }
        }

        return [
            'service_ports' => $servicePorts,
            'traffic_policies' => $traffic,
        ];
    }

    private function parseOnuShowThis(string $out): array
    {
        $gemportVlans = []; // gemport => [vlans]
        $ethVlans = [];     // eth_0/1 => [vlans]
        $ethMode = [];      // eth_0/1 => mode

        // service 1 gemport 1 vlan 2543
        if (preg_match_all('/^\s*service\s+\d+\s+gemport\s+(\d+)\s+vlan\s+(\d+)\s*$/mi', $out, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $gp = (int)$m[1];
                $v = (int)$m[2];
                $gemportVlans[$gp][] = $v;
            }
        }

        // vlan port eth_0/1 mode tag vlan 2543
        if (preg_match_all('/^\s*vlan\s+port\s+(eth_0\/\d+)\s+mode\s+(\S+)(?:\s+vlan\s+(\d+))?\s*$/mi', $out, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $eth = strtolower($m[1]);
                $mode = strtolower($m[2]);
                $ethMode[$eth] = $mode;

                if (($m[3] ?? '') !== '') {
                    $ethVlans[$eth][] = (int)$m[3];
                }
            }
        }

        // нормализация
        foreach ($gemportVlans as $gp => $vlans) {
            $gemportVlans[$gp] = $this->uniqSortedInts($vlans);
        }
        foreach ($ethVlans as $eth => $vlans) {
            $ethVlans[$eth] = $this->uniqSortedInts($vlans);
        }

        ksort($gemportVlans);
        ksort($ethVlans);
        ksort($ethMode);

        return [
            'gemport_vlans' => $gemportVlans,
            'eth_vlans' => $ethVlans,
            'eth_mode' => $ethMode,
        ];
    }

    // ---------------- HELPERS ----------------

    private function parseVlanSection(string $out, string $sectionName): array
    {
        $re = '/^\s*' . preg_quote($sectionName, '/') . '\s*:\s*(.*?)(?=^\s*[A-Za-z][A-Za-z0-9_-]*\s*:\s*$|\z)/ms';
        if (!preg_match($re, $out, $m)) {
            return [];
        }
        if (!preg_match_all('/\b(\d{1,5})\b/', $m[1], $mm)) {
            return [];
        }
        return $this->uniqSortedInts(array_map('intval', $mm[1]));
    }

    private function uniqSortedInts(array $vals): array
    {
        $vals = array_values(array_unique(array_map('intval', $vals)));
        sort($vals, SORT_NUMERIC);
        return $vals;
    }
}