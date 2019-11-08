<?php


namespace SwitcherCore\Switcher;


use SnmpWrapper\Device;
use SnmpWrapper\MultiWalker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;

class CoreConnector
{
    protected  $proxyConfigPath;
    protected  $configPath;
    protected  $telnetPort = 23;
    protected  $mikrotikApi = 55055;
    protected static $instances = [];
    public function __construct($configPath, $proxyConfigPath)
    {
        $this->proxyConfigPath = $proxyConfigPath;
        $this->configPath = $configPath;
    }


    public function setTelnetPort($port) {
        $this->telnetPort = $port;
        return $this;
    }
    public function setMikrotikApiPort($port) {
        $this->mikrotikApi = $port;
        return $this;
    }
    public function getProxyConfigPath() {
        return $this->proxyConfigPath;
    }
    public function getTelnetPort() {
        return $this->telnetPort;
    }
    public function getMikrotikApiPort() {
        return $this->mikrotikApi;
    }

    /**
     * @param $ip
     * @param $community
     * @param $login
     * @param $password
     * @return Core
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException
     * @throws \SwitcherCore\Exceptions\ModuleNotFoundException
     * @throws \Exception
     */
    public function getOrInit($ip, $community, $login, $password) {
        if(isset(self::$instances[$ip])) {
            return self::$instances[$ip];
        }
        return $this->init($ip, $community, $login, $password);
    }

    /**
     * @param $ip
     * @return bool
     */
    public function isConnected($ip) {
        return isset(self::$instances[$ip]);
    }

    public function get($ip) {
        if(self::isConnected($ip)) {
            return self::$instances[$ip];
        }
        throw new \Exception("Connection to $ip not existed");
    }

    /**
     * @param $ip
     * @param $community
     * @param $login
     * @param $password
     * @return Core
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException
     * @throws \SwitcherCore\Exceptions\ModuleNotFoundException
     * @throws \Exception
     */
    public function init($ip, $community, $login, $password) {

        $walker = $this->initWalker($ip, $community);

        $core = (new \SwitcherCore\Switcher\Core(
            new  Reader($this->configPath)
        ))->addInput($walker)->init();

        $inputs_list = $core->getNeedInputs();
        if(in_array('telnet', $inputs_list)) {
            $telnet = $this->initTelnet($ip,$login,$password);
            $core->addInput($telnet);
        }

        if(in_array('routeros_api', $inputs_list)) {
            $routerOS = $this->initMikrotikApi($ip, $login,$password);
            $core->addInput($routerOS);
        }
        self::$instances[$ip] = $core;
        return $core;
    }

    /**
     * @param $ip
     * @return \SwitcherCore\Config\ProxyConfiguration
     * @throws \Exception
     */
    private function getProxyConfig($ip) {
        $proxyConfig = new \SwitcherCore\Config\ProxyConfiguration($this->proxyConfigPath);
        return $proxyConfig->setSearchedIP($ip);
    }

    private function initWalker($ip, $community) {
        $wrapper =  new  WrapperWorker(
            $this->getProxyConfig($ip)
                ->getSnmpConfiguration()['address']
        );
        return (new  MultiWalker($wrapper))->addDevice(
            Device::init(
                $ip,
                $community,
                $this->getProxyConfig($ip)
                    ->getSnmpConfiguration()['timeout']
            )
        );
    }
    private function initTelnet($ip, $login,$password) {
        return (new \SwitcherCore\Switcher\Objects\TelnetLazyConnect($ip, $this->getTelnetPort()))
            ->connectOverProxy($this->getProxyConfig($ip)->getTelnetConfiguration()['address'])
            ->login($login, $password);
    }
    private function initMikrotikApi($ip, $login,$password) {
        return (new \SwitcherCore\Switcher\Objects\RouterOsLazyConnect())
            ->setPort($this->getMikrotikApiPort())
            ->connect($ip, $login, $password);
    }
}