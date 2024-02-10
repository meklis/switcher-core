<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

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
            $this->response = $this->allPonFdbByConsole();
            return $this;
        }
        return $this;
    }

    public function allPonFdbByConsole()
    {
        if ($cached = $this->getCache('all_pon_fdb_table', true)) {
            return $cached;
        }
        $this->console->exec("enable", true, "^Password:");
        if (strpos($this->console->exec($this->device->getPassword()), "password") !== false) {
            throw new \Exception("Error enable command");
        }
        $this->console->exec("conf t");
        $lines = $this->console->exec("show mac address-table pon");

        $vlan_id = $mac = $is_dynamic = null;
        $fdb = [];
        foreach (explode("\n", $lines) as $line) {
            $line = trim($line);
            if (preg_match('/^[[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4}$/', $line, $m)) {
                $mac = implode(':', str_split(str_replace('.', '', $m[0]), 2));
            } elseif (preg_match('/^[0-9]{1,4}$/', substr($line, 4), $m)) {
                $vlan_id = $m[0];
            } elseif (preg_match('/^Dynamic|Static$/', substr($line, 4), $m)) {
                $is_dynamic = ($m[0] == 'Dynamic') ? 'true' : 'false';
            } elseif (preg_match('/^EPON|GPON([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', substr($line, 4), $m)) {
                $iface = $this->parseInterface(str_replace($m[3], (int)$m[3], $m[0]));
                $fdb[] = [
                    'interface' => $iface,
                    'vlan_id' => (int)$vlan_id,
                    'mac_address' => strtoupper($mac),
                    'is_dynamic' => $is_dynamic,
                ];
                continue;
            }
        }
        $this->setCache('all_pon_fdb_table', $fdb, 60);
        return $fdb;
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
            } elseif (preg_match('/^[0-9]{1,4}$/', substr($line, 4), $m)) {
                $vlan_id = $m[0];
            } elseif (preg_match('/^Dynamic|Static$/', substr($line, 4), $m)) {
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
        return $fdb;
    }

}

