<?php


namespace SwitcherCore\Modules\VsolOlts;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsole extends VsolOltsAbstractModule
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
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
               return $e['interface']['id'] == $interface['id'];
            });
        }
        if($filter['mac']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac']);
            });
        }
        if($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        return $data;
    }

    function getPretty()
    {
        return $this->response;
    }


    public function run($filter = [])
    {
        if ($filter['mac']) {
            $mac = Helper::formatMac3Blocks($filter['mac']);
            $command = "show mac address-table {$filter['mac']}";
        } elseif ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $command = "show mac address-table interface {$iface['name']}";
        } elseif ($filter['vlan_id']) {
            $iface = $this->parseInterface($filter['vlan_id']);
            $command = "show mac address-table vlan {$iface['vlan_id']}";
        } else {
            $command = "show mac address-table";
        }
        $result = $this->console->exec($command);
        $response = [];
        foreach (explode("\n", $result) as $line) {
            if(preg_match('/^([0-9]{1,4})\s*?([[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4})\s*(.*?)\s*(\S*)$/', trim($line), $m)) {
                $iface = $this->parseInterface($m[4]);
                $response[] = [
                    'interface' => $iface,
                    'vlan_id' => (int)$m[1],
                    'is_dynamic' => $m[3] == 'DYNAMIC',
                    'mac_address' => Helper::formatMac($m[2]),
                ];
            }
        }
        $this->response = $response;
        return $this;
    }
}

