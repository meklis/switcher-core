<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsole extends HuaweiOLTAbstractModule
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
        $commands = [];
        if ($filter['mac']) {
            $mac = Helper::formatMac3Blocks($filter['mac']);
            $commands[] = "show mac address-table {$mac}";
        } elseif ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $commands[] = "display mac-address port {$iface['_frame']}/{$iface['_slot']}/{$iface['_port']}";
        } elseif ($filter['vlan_id']) {
            $commands[] = "show mac address-table vlan {$filter['vlan_id']}";
        } else {
            $ifaces = $this->getInterfaces();
            foreach ($ifaces as $iface) {
                $commands[] = "display mac-address port {$iface['_frame']}/{$iface['_slot']}/{$iface['_port']}";
            }
        }

        $response = [];
        foreach ($commands as $command) {
            $result = $this->console->exec($command);
            foreach (explode("\n", $result) as $line) {
                if (preg_match('/^(-|[0-9]{1,5})\s*?[\S*?]\s*?(eth|gpon)\s.*?([[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4})\s.*?(\S*?)\s*?([0-9].*?\/[0-9].*?\/[0-9]{1,3})\s*?(-|[0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?(-|[0-9]{1,4})$/', trim($line), $m)) {
                    if (trim($m[6]) == '-') {
                        $iface = $this->parseInterface('ethernet' . str_replace(" ", "", $m[5]));
                    } else {
                        $iface = $this->parseInterface("GPON " . str_replace(" ", "", $m[5]) . ":" . $m[6]);
                    }
                    $response[] = [
                        'interface' => $iface,
                        'vlan_id' => (int)$m[8],
                        'is_dynamic' => $m[4] == 'dynamic',
                        'mac_address' => Helper::formatMac($m[3]),
                        'gem_port' => (int)$m[6],
                    ];
                }
            }
        }
        $this->response = $response;
        return $this;
    }
}

