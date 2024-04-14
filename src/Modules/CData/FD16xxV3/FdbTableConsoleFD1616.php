<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsoleFD1616 extends CDataAbstractModuleFD16xxV3
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

        if($filter['mac']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac']);
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
        if($filter['vlan_id']) {
            $resp = $this->console->exec("show mac-address vlan {$filter['vlan']}");
        } elseif ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if(strpos($iface['_type'], "pon") !== false) {
                $iface['_type'] = "pon";
            }
            $telnetIdent = "{$iface['_type']} 0/0/{$iface['_port']}";
            if ($iface['type'] === 'ONU') {
                $resp = $this->console->exec("show mac-address port {$telnetIdent} ont {$iface['_onu_num']}");
            } else {
                $resp = $this->console->exec("show mac-address port {$telnetIdent}");
            }
        } else {
            $resp = $this->console->exec("show mac-address all");
        }
        $responses = [];
        foreach (explode("\n", $resp) as $line) {
            if (preg_match('/^([[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2})\s*?([0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?((ge|xe|gpon)[0-9]\/[0-9]\/[0-9]{1,3})\s*?(-|[0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?(\S*)$/', trim($line), $match)) {
                if($match[7] == '-') {
                    $iface = $this->parseInterface($match[5]);
                }  else {
                    $iface = $this->parseInterface("{$match[5]}:{$match[7]}");
                }
                $responses[] = [
                    'mac_address' => $match[1],
                    'vlan_id' => (int)$match[2],
                    'interface' => $iface,
                    '_type' => $match[9] == '-' ? null :  $match[9],
                    '_gem' => $match[8] == '-' ? null :  $match[8],
                    'uni' => $match[4] == '-' ? null :  $match[4],
                ];
            }
        }
        $this->response = $responses;
        return $this;
    }

}

