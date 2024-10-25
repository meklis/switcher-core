<?php


namespace SwitcherCore\Modules\CData;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsole extends CDataAbstractModule
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
        $ifaces = [];
        if ($filter['interface']) {
            $ifaces[] = $this->parseInterface($filter['interface']);
        } else {
            $ifaces = $this->getModule('pon_ports_list')->run()->getPretty();
        }

        $this->response = $this->executeByInterfaces($ifaces);
        return $this;
    }

    protected function executeByInterfaces($ifaces)
    {
        $responses = [];
        foreach ($ifaces as $iface) {
            if (!preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]{1,3})\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $iface['name'], $m)) {
                continue;
            }
            if($iface['pontype']) {
                $m[1] = $iface['pontype'];
            }
            $telnetIdent = "{$m[1]} {$m[2]}/{$m[3]}/{$m[4]}";

            if ($iface['type'] === 'ONU') {
                $resp = $this->console->exec("show mac-address ont {$m[2]}/{$m[3]}/{$m[4]} {$m[5]}");
                foreach (explode("\n", $resp) as $line) {
                    if (preg_match('/^([[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2})\s*([0-9]{1,4})\s*(\S*)\s*([0-9]{1,3})\s*(\S*)$/', trim($line), $match)) {
                        $responses[] = [
                            'mac_address' => $match[1],
                            'vlan_id' => (int)$match[2],
                            'interface' => $this->parseInterface($match['3'] . ":" . $match[4])
                        ];
                    }
                }
            } elseif ($iface['type'] === 'PON') {
                sleep(0.1);
                $resp = $this->console->exec("show mac-address port {$telnetIdent}  with-ont-location");
                foreach (explode("\n", $resp) as $line) {
                    if (preg_match('/^([[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2})\s*([0-9]{1,4})\s*(\S*)\s*([0-9]{1,3})\s*(\S*)$/', trim($line), $match)) {
                        $responses[] = [
                            'mac_address' => $match[1],
                            'vlan_id' => (int)$match[2],
                            'interface' => $this->parseInterface($match['3'] . ":" . $match[4])
                        ];
                    }
                }
            } else {
                $resp = $this->console->exec("show mac-address port {$telnetIdent}");
                foreach (explode("\n", $resp) as $line) {
                    $line = str_replace("--More ( Press 'Q' to quit )--", "", $line);
                    if (preg_match('/^([[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2}:[[:xdigit:]]{2})\s*([0-9]{1,4})\s*(\S*)\s*(\S*)$/', trim($line), $match)) {
                       $responses[] = [
                            'mac_address' => $match[1],
                            'vlan_id' => (int)$match[2],
                            'interface' => $this->parseInterface($match['3'])
                        ];
                    }
                }
            }
        }
        return $responses;
    }
}

