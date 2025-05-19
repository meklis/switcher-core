<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class Fdb extends AbstractModule
{
    use InterfacesTrait;


    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    /**
     * @param $params
     * @return $this|AbstractModule
     * @throws \Exception
     */
    public function run($params = [])
    {
        /**
         * - {name: ip, pattern: '^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$', required: no}
         * - {name: vlan_id, pattern: '^[0-9]{1,4}$', required: no}
         * - {name: vlan_name, pattern: '^.*$', required: no}
         * - {name: interface, pattern: '^.*$', required: no}
         * - {name: mac, pattern: '^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$', required: no}
         * - {name: status, pattern: '^(disabled|invalid|OK)$', required: no, values: [disabled, invalid, OK]}
         */
        $command = "show fdb";
        if(isset($params['vlan_name']) && $params['vlan_name']) {
            $command .= " vlan {$params['vlan_name']}";
        }
        if(isset($params['mac']) && $params['mac']) {
            $command .= " {$params['mac']}";
        }

        $this->response = $this->console->exec($command);
        return $this;
    }

    public function getPretty()
    {

        $lines = explode("\n", $this->response);
        array_shift($lines);
        $parsedData = [];

        foreach ($lines as $line) {
            if(!preg_match('/^(([[:xdigit:]]{2}\:){5}[[:xdigit:]]{2})[ \t]*(.*?)\(([0-9]{1,4})\).*?([0-9]{1,3})$/', $line, $m)) {
                continue;
            }
            try {
                $parsedData[] = [
                    'mac' => strtoupper($m[1]),
                    '_vlan_name' => $m[3],
                    'vlan_id' => (int)$m[4],
                    'interface' => $this->parseInterface($m[5]),
                ];
            } catch (\Exception $e) {}
        }
        return $parsedData;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}