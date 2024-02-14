<?php

namespace SwitcherCore\Modules\VsolOlts\GPONV1600;

use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTable extends VsolOltsAbstractModule
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        return array_values($data);
    }

    function getPretty()
    {
        return $this->response;
    }

    public function run($filter = [])
    {
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if ($iface['type'] != 'ONU') {
                throw new \InvalidArgumentException("Only ONU allowed to set interface");
            }
            $this->response = $this->byOnu($iface);
        } else {
            $this->response = $this->allFdbByConsole();
            return $this;
        }
        return $this;
    }

    public function byOnu($iface)
    {

        $this->console->exec("conf t");
        $lines = $this->console->exec("show mac address-table pon {$iface['_port']} {$iface['_onu']}");

        $vlan_id = $mac = $is_dynamic = '';
        $fdb = [];
        foreach (explode("\n", $lines) as $line) {
            $line = trim($line);
            if (preg_match('/^[[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4}$/', $line, $m)) {
                $mac = Helper::formatMac($m[0]);
            } else {
                if ($line = substr($line, 4)) {
                    if (preg_match('/^[0-9]{1,4}$/', $line, $m)) {
                        $vlan_id = $m[0];
                    } elseif (preg_match('/^Dynamic|Static$/', $line, $m)) {
                        $is_dynamic = ($m[0] == 'Dynamic') ? 'true' : 'false';
                        $fdb[] = [
                            'interface' => $iface,
                            'vlan_id' => (int)$vlan_id,
                            'mac_address' => strtoupper($mac),
                            'is_dynamic' => $is_dynamic,
                        ];
                        continue;
                    }
                }
            }
        }
        return $fdb;
    }

    public function allFdbByConsole()
    {
        if ($cached = $this->getCache('all_fdb_table', true)) {
            return $cached;
        }
        $this->console->exec("enable", true, "^Password:");
        if (strpos($this->console->exec($this->device->getPassword()), "password") !== false) {
            throw new \Exception("Error enable command");
        }
        $this->console->exec("conf t");
        $lines = $this->console->exec("show mac address-table");

        $vlan_id = $mac = $is_dynamic = '';
        $fdb = [];
        foreach (explode("\n", $lines) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (isset($parts[0]) && preg_match('/^[0-9]{1,4}$/', $parts[0], $m)) {
                $vlan_id = $m[0];
            }
            if (isset($parts[1]) && preg_match('/^[[:xdigit:]]{4}:[[:xdigit:]]{4}:[[:xdigit:]]{4}$/', $parts[1], $m)) {
                $mac = Helper::formatMac($m[0]);
            }
            if (isset($parts[2]) && preg_match('/^Dynamic|Static$/', $parts[2], $m)) {
                $is_dynamic = ($m[0] == 'Dynamic') ? 'true' : 'false';
            }
            if (isset($parts[3]) && isset($parts[4]) && preg_match('/^EPON|GPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $parts[3] . $parts[4], $m)) {
                $iface = $this->parseInterface(str_replace($m[3], (int)$m[3], $m[0]));
                $fdb[] = [
                    'interface' => $iface,
                    'vlan_id' => (int)$vlan_id,
                    'mac_address' => strtoupper($mac),
                    'is_dynamic' => $is_dynamic,
                ];
                continue;
            }
            if (isset($parts[3]) && isset($parts[4]) && preg_match('/^GE([0-9]{1,3})\/[0-9]{1,3}$/', $parts[3] . $parts[4], $m)) {
                $last_char = substr($m[0], -1);
                $name = "GE0/$last_char";
                $iface = $this->parseInterface($name);
                $fdb[] = [
                    'interface' => $iface,
                    'vlan_id' => (int)$vlan_id,
                    'mac_address' => strtoupper($mac),
                    'is_dynamic' => $is_dynamic,
                ];
                continue;
            }
        }
        $this->setCache('all_fdb_table', $fdb, 60);
        return $fdb;
    }
}

