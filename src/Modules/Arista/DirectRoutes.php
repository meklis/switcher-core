<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class DirectRoutes extends AbstractModule
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;
    public function run($params = [])
    {
        $interfaces = json_decode($this->console->exec("show ip interface | json"), true);
        if(json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception("Error in json parser: " . json_last_error_msg());
        }
        if(!isset($interfaces['interfaces'])) {
            throw new \Exception("Interfaces not defined");
        }
        $directRoutes = [];
        foreach ($interfaces['interfaces'] as $ifaceName=>$detailed) {
            if(!preg_match("/^Vlan([0-9]{1,4})$/i", $ifaceName, $vlanMatch)) {
                continue;
            }
            if(!$detailed['enabled']) {
                continue;
            }
            $basicValues = [
                'type' => 'v4',
                'vlan_id' => (int)$vlanMatch[1],
                'vlan_name' => $detailed['description'],
                'network' => null,
                'broadcast' => null,
                'gateway' => null,
                'cidr' => null,
            ];
            if(isset($detailed['interfaceAddress']['primaryIp'])) {
                $directRoutes[] = array_merge(
                    $basicValues,
                    $this->getNetworkDetailByGatewayAndPrefix(
                        $detailed['interfaceAddress']['primaryIp']['address'], $detailed['interfaceAddress']['primaryIp']['maskLen']
                    )
                );
            }
            if(isset($detailed['interfaceAddress']['secondaryIpsOrderedList']) && $detailed['interfaceAddress']['secondaryIpsOrderedList']) {
                foreach ($detailed['interfaceAddress']['secondaryIpsOrderedList'] as $address) {
                    $directRoutes[] = array_merge(
                        $basicValues,
                        $this->getNetworkDetailByGatewayAndPrefix(
                            $address['address'], $address['maskLen']
                        )
                    );
                }
            }
        }
        $this->response = $directRoutes;
        return $this;
    }

    function getNetworkDetailByGatewayAndPrefix($gateway, $prefix)
    {
        $ipLong = ip2long($gateway);
        if ($ipLong === false) {
            return null;
        }
        $maskLong = $prefix === 0 ? 0 : (0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF;
        $networkLong = $ipLong & $maskLong;
        $broadcastLong = $networkLong | (~$maskLong & 0xFFFFFFFF);
        $network =  long2ip($networkLong);
        return [
            'network' => $network,
            'broadcast' => long2ip($broadcastLong),
            'gateway' => $gateway,
            'cidr' => "{$network}/{$prefix}",
        ];
    }


    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}