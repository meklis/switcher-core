<?php


namespace SwitcherCore\Modules\CData\FD17xxV3;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsoleFD17xx extends CDataAbstractModuleFD17xxV3
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
            $resp = $this->console->exec("show mac-address vlan {$filter['vlan_id']}");
        } elseif ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if(strpos($iface['_type'], "pon") !== false) {
                $iface['_type'] = "pon";
            }
            $telnetIdent = "{$iface['_type']} 0/{$iface['_slot']}/{$iface['_port']}";
            if ($iface['type'] === 'ONU') {
                $resp = $this->console->exec("show mac-address port {$telnetIdent} ont {$iface['_onu']}");
            } else {
                $resp = $this->console->exec("show mac-address port {$telnetIdent}");
            }
        } else {
            $resp = $this->console->exec("show mac-address all");
        }
        $responses = [];
        foreach (explode("\n", $resp) as $line) {
            //  MAC                   VLAN    Sport   Port           Onu     Gemid   MAC-Type
            //  98:49:25:40:EC:A8     91      -       eth0/0/2       -       -       dynamic
            //  08:55:31:1F:99:02     2101    1       gpon0/2/1      1       1       dynamic
            if (preg_match('/^([\dA-F]{2}:[\dA-F]{2}:[\dA-F]{2}:[\dA-F]{2}:[\dA-F]{2}:[\dA-F]{2})\s*?([0-9]{1,4})\s*?(-|\d{1,4})\s*?((ge|xe|gpon|eth)\d\/\d\/\d{1,3})\s*?(-|\d{1,4})\s*?(-|\d{1,4})\s*?(\S*)$/', trim($line), $match)) {
                if($match[6] == '-') {
                    $iface = $this->parseInterface($match[4]);
                }  else {
                    $iface = $this->parseInterface("{$match[4]}:{$match[6]}");
                }
                $responses[] = [
                    'mac_address' => $match[1],
                    'vlan_id' => (int)$match[2],
                    'interface' => $iface,
                    '_type' => $match[8] == '-' ? null :  $match[8],
                    '_gem' => $match[7] == '-' ? null :  $match[7],
                    'uni' => $match[3] == '-' ? null :  $match[3],
                ];
            }
        }
        $this->response = $responses;
        return $this;
    }

}

