<?php

namespace SwitcherCore\Modules\Huawei;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    function run($filter = [])
    {
        /**
         * ^([[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4})\s*?([0-9]{1,4})\/.*?[ ]{1,}(XGE|GE|Eth-|.*?)[ ]{1,}(\S.*)$
         */
        $command = 'display mac-address';
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if(preg_match('/^(GigabitEthernet|XGigabitEthernet|Ethernet)([0-9]{1,3})\/([0-9]{1,3})\/([0-9]{1,3})$/', $iface['name'], $m)) {
                $command = "display  mac-address {$m[1]} {$m[2]}/{$m[3]}/{$m[4]}";
            } elseif (preg_match('/^Eth-Trunk([0-9]{1,3})/', $iface['name'], $m)) {
                $command = "display mac-address Eth-Trunk {$m[1]}";
            } elseif (preg_match('/^eth([0-9]{1,3})/', $iface['name'], $m)) {
                $command = "display mac-address {$iface['_type']} 0/0/{$m[1]}";
            } elseif (preg_match('/^Ge([0-9]{1,3})/', $iface['name'], $m)) {
                $command = "display mac-address {$iface['_type']} 0/0/{$m[1]}";
            } else {
                throw  new \Exception("Incorrect interface for get FDB");
            }
        }
        $data = $this->console->exec($command);
        $interfacesList = [];
        foreach ($this->getInterfacesIds() as $iface) {
            if(!$iface['_short_name']) continue;
            $interfacesList[$iface['_short_name']] = $iface;
        }


        $fdb = [];
        foreach (explode("\n", $data) as $line) {
            if(preg_match('/^([[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4})\s*?([0-9]{1,4})\/.*?[ ]{1,}(XGE|GE|Eth-|.*?)[ ]{1,}(\S.*)$/', trim($line), $matches)) {
                if(!isset($interfacesList[$matches[3]])) {
                    continue;
                }
                $fdb[] = [
                    'interface' => $interfacesList[$matches[3]],
                    'vlan_id' => (int)$matches[2],
                    'mac_address' => Helper::formatMac($matches[1]),
                    'status' => $matches[4],
                ];
            }
        }
        $this->response = $fdb;
        return $this;
    }
    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
