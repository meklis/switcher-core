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
            /*$oidList[] = $this->oids->getOidByName('pon.macPhysicalPortName');
            $oidList[] = $this->oids->getOidByName('pon.PhysAddr');
            $oidList[] = $this->oids->getOidByName('pon.macVlanId');
            $oidList[] = $this->oids->getOidByName('pon.macType');
            $oids = [];
            foreach ($oidList as $oid) {
                $oids[] = $oid->getOid();
            }
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);

            if ($cached = $this->getCache('all_fdb_table', true)) {
                $this->response = $cached;
            } else {
                $this->response = $this->allFdb($this->formatResponse($this->snmp->walk($oids)));
            }*/
        }
        return $this;
    }

    public function byOnu($iface)
    {
        $this->console->exec("enable", true, "^Password:");
        if (strpos($this->console->exec($this->device->getPassword()), "password") !== false) {
            throw new \Exception("Error enable command");
        }
        $this->console->exec("conf t");
        $lines = $this->console->exec("show mac address-table pon {$iface['_port']} {$iface['_onu']}");

        $vlan_id = $mac = $is_dynamic = '';
        $fdb = [];
        foreach (explode("\n", $lines) as $line) {
            $line = trim($line);
            if (preg_match('/^[[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4}$/', $line, $m)) {
                $mac = implode(':', str_split(str_replace('.', '', $m[0]), 2));
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

    public function allFdb($formatted_resp)
    {
        $this->response = $formatted_resp;
        $ifaces = [];
        $data = $this->getResponseByName('pon.macPhysicalPortName');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $id_from_oid = Helper::getIndexByOid($r->getOid());
                $name = $r->getParsedValue();
                if (preg_match('/^PON([0-9]{1,3}):ONU([0-9]{1,3})$/', $name, $matches)) {
                    $name = "GPON0/$matches[1]:$matches[2]";
                } elseif (preg_match('/^GE([0-9]{1,3})$/', $name, $matches)) {
                    $name = "GE0/$matches[1]";
                } else {
                    continue;
                }
                $iface = $this->parseInterface($name);
                if (!$iface) continue;
                $ifaces[$id_from_oid]['interface'] = $iface;
            }
        }

        $data = $this->getResponseByName('pon.PhysAddr');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $id_from_oid = Helper::getIndexByOid($r->getOid());
                if (isset($ifaces[$id_from_oid]['interface'])) {
                    $ifaces[$id_from_oid]['mac_address'] = str_replace(' ', ":", $r->getParsedValue());
                }
            }
        }

        $data = $this->getResponseByName('pon.macVlanId');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $id_from_oid = Helper::getIndexByOid($r->getOid());
                if (isset($ifaces[$id_from_oid]['interface'])) {
                    $ifaces[$id_from_oid]['vlan_id'] = (int)$r->getParsedValue();
                }
            }
        }

        $data = $this->getResponseByName('pon.macType');
        if (!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $id_from_oid = Helper::getIndexByOid($r->getOid());
                if (isset($ifaces[$id_from_oid]['interface'])) {
                    $ifaces[$id_from_oid]['is_dynamic'] = ($r->getParsedValue()) ? true : false;
                }
            }
        }

        $this->setCache('all_fdb_table', array_values($ifaces), 120);
        return array_values($ifaces);
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
                $mac = implode(':', str_split(str_replace(':', '', $m[0]), 2));
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

