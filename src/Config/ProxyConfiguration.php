<?php


namespace SwitcherCore\Config;


use IPv4\SubnetCalculator;

class ProxyConfiguration
{
    protected $object;
    protected $networks;
    protected $searchedNetwork;
    function __construct($configPath = "")
    {
        if(!$this->object = yaml_parse_file($configPath)) {
            throw new \Exception("Error read yaml configuration for proxy");
        }
        $this->prepareNetworkSearching();
    }
    protected function prepareNetworkSearching()
    {
        $networks = [];
        foreach ($this->object as $proxies) {
            foreach ($proxies['networks'] as $network) {
                if(isset($networks[$network])) {
                    throw new \InvalidArgumentException("Duplicate network $network in configuration");
                }
                list($network, $cidr) = explode('/', $network);
                $sub = new SubnetCalculator($network, $cidr);
                $networks[$network] = [
                    '_network' => $network,
                    '_cidr' => $cidr,
                    'snmp' => $proxies['snmp'],
                    'telnet' => $proxies['telnet'],
                    '_network_size' => $sub->getNetworkSize(),
                ];
            }
        }
        usort($networks, function ($a, $b){
            if ($a['_network_size'] == $b['_network_size']) {
                return 0;
            }
            return ($a['_network_size'] > $b['_network_size']) ? -1 : 1;
        });
        $this->networks = array_values($networks);
    }
    function setSearchedIP($searched_ip) {
        foreach ($this->networks as $network) {
            $sub = new SubnetCalculator($network['_network'], $network['_cidr']);
            if($sub->isIPAddressInSubnet($searched_ip)) {
                $this->searchedNetwork = $network;
                return $this;
            }
        }
        throw new \Exception("Network with its IP not found in proxy configuration");
    }
    function getSnmpConfiguration() {
       if($this->isIpSetted()) {
            return $this->searchedNetwork['snmp'];
       } else {
           throw new \Exception("IP not setted");
       }
    }
    function getTelnetConfiguration() {
        if($this->isIpSetted()) {
            return $this->searchedNetwork['telnet'];
        } else {
            throw new \Exception("IP not setted");
        }
    }
    private function isIpSetted() {
        if(!$this->searchedNetwork) {
            throw new \Exception("IP not setted");
        }
        return true;
    }
}