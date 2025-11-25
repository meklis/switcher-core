<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SwitcherCore\Modules\Helper;

class Fdb extends ModuleAbstract
{
    public function run($params = [])
    {
        $data = [];
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $data = $this->fdbByInterface($interface);
        } elseif($params['vlan_id']) {
            $data = $this->fdbByVlan($params['vlan_id']);
        } else {
            $interfaces = $this->getModule('pon_ports_list')->run()->getPretty();
            foreach ($interfaces as $interface) {
                $data = array_merge($data, $this->fdbByInterface($interface));
            }
        }
        $this->response = $data;
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

    public function fdbByVlan($vlan_id) {
        $command = "show mac vlan $vlan_id";
        $lines = explode("\n", $this->telnet->exec($command));
        $resp = [];
        foreach ($lines as $line) {
            if(preg_match('/((([0-9a-f]{4})\.?){3})\s+(\d{1,4})\s+(dynamic|static)\s+((\w+)[-_]?(\w+)?[-_]?\d\/\d{1,3}(\/\d{1,3})?([:\.])?(\d{1,3})?(:?(\d)?)).*/i', trim($line), $m)) {
                $resp[] = [
                    '_virtual_port' => isset($m[13]) ? $m[13] : null,
                    'mac_address' => Helper::formatMac($m[1]),
                    'vlan_id' => $vlan_id,
                    'type' => strtoupper($m[5]),
                    'interface' => $this->parseInterface($m[6]),
                ];
            }
        }
        return $resp;
    }

    function fdbByInterface($interface)
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }

        $command = "show mac interface {$interface['name']}";

        $lines = explode("\n", $this->telnet->exec($command));
        if (preg_match('/Total mac address : ([0-9]{1,})/', trim($lines[0]), $matches)) {
            if ($matches[1] == 0) {
                return [];
            }
        }
        $lines = array_splice($lines, 2);
        $data = [];
        foreach ($lines as $line) {
            $response = [];
            if (preg_match('/^([[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4})[ ]{1,}([0-9]{1,4})[ ]{1,}(\S*)[ ]{1,}.*?-(([0-9])\/([0-9]{1,3})\/([0-9]{1,3}))\.([0-9]{1,3}):?([0-9]{1,3})?.*?$/', trim($line), $m)) {
                if (isset($m[9])) {
                    $response['_virtual_port'] = $m[9];
                } else {
                    $response['_virtual_port'] = null;
                }
                $response['mac_address'] = $this->macTo6octets($m[1]);
                $response['vlan_id'] = (int)$m[2];
                $response['type'] = $m[3];
                $response['interface'] = $this->parseInterface("{$m[4]}:{$m[8]}");
                $data[] = $response;
            }
        }
        return $data;
    }
}