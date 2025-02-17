<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class ArpInfo extends AbstractModule
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
        $command = "show iparp";
        if(isset($params['ip']) && $params['ip']) {
            $command .= " {$params['ip']}";
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
            $columns = preg_split('/\s{2,}/', trim($line)); // Разделяем строку на столбцы
            if(!isset($columns[7])) {
                continue;
            }
            if(!preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/', $columns[1])) {
                continue;
            }
            try {
                $parsedData[] = [
                    '_virtual_router' => $columns[0],
                    'ip' => $columns[1],
                    'mac' => strtoupper($columns[2]),
                    '_age' => (int)$columns[3],
                    '_static' => $columns[4] === 'YES',
                    '_vlan_name' => $columns[5],
                    'vlan_id' => (int)$columns[6],
                    'interface' => $this->parseInterface($columns[7]),
                ];
            } catch (\Exception $e) {}
        }
        /**
         * 'interface' => $a['interface'],
         * 'ip' => $a['address'],
         * 'mac' => isset($a['mac-address']) ? $a['mac-address'] : null,
         * 'dynamic' => $a['dynamic'] == 'true',
         * 'dhcp' => isset($a['dhcp']) ? $a['dhcp'] == 'true' : null,
         * 'disabled' => isset($a['disabled']) ? $a['disabled'] == 'true' : null,
         * 'published' => isset($a['published']) ? $a['published'] == 'true' : null,
         * 'invalid' => isset($a['published']) ? $a['invalid'] == 'true' : null,
         * 'comment' => isset($a['comment']) ? $a['comment'] : "",
         * 'vlan_id' => isset($vlans[$a['interface']]['vlan_id']) ? (int)$vlans[$a['interface']]['vlan_id']: -1,
         * 'status' => $status,
         * 'extra' => [
         * 'id' => $a['.id'],
         * 'interface_name' => $a['interface'],
         * ]
         */
        return $parsedData;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}