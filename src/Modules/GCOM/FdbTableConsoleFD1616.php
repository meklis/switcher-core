<?php


namespace SwitcherCore\Modules\GCOM;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsoleFD1616 extends GCOMAbstractModule
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
            $interface = $this->parseInterface($filter['interface']);
            if (!preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $interface['name'], $m)) {
                throw new \Exception("Interface {$filter['interface']} not supported!");
            }
            if($interface['pontype']) {
                $m[1] = $interface['pontype'];
            }
            $telnetIdent = "{$m[1]} {$m[2]}/{$m[3]}/{$m[4]}";
            if ($interface['type'] === 'ONU') {
                $resp = $this->console->exec("show mac-address port {$telnetIdent} ont {$m[5]}");
            } else {
                $resp = $this->console->exec("show mac-address port {$telnetIdent}");
            }
        } else {
            $resp = $this->console->exec("show mac-address all");
        }
        $responses = [];
        foreach (explode("\n", $resp) as $line) {
            if (preg_match('/^([[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2})\s*?([0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?((ge|xge|pon)[0-9]\/[0-9]\/[0-9]{1,3})\s*?(-|[0-9]{1,4})\s*?(-|[0-9]{1,4})\s*?(\S*)$/', trim($line), $match)) {
                if($match[6] == '-') {
                    $iface = $this->parseInterface($match[4]);
                }  else {
                    $iface = $this->parseInterface("{$match[4]}:{$match[6]}");
                }
                $responses[] = [
                    'mac_address' => $match[1],
                    'vlan_id' => $match[2],
                    'interface' => $iface,
                    'status' => $match[8] == '-' ? null :  $match[8],
                    '_sport' => $match[3] == '-' ? null :  $match[3],
                    '_gemid' => $match[7] == '-' ? null :  $match[7],
                ];
            }
        }
        $this->response = $responses;
        return $this;
    }

}

