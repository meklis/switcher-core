<?php


namespace SwitcherCore\Switcher;


use SnmpWrapper\Device;
use SnmpWrapper\NoProxy\MultiWalker;
use SwitcherCore\Config\Reader;

class CoreConnector
{
    protected  $configPath;
    protected  $telnetPort = 23;
    protected  $mikrotikApi = 55055;
    protected $snmp_timeout_sec = 2;
    protected $snmp_repeats = 3;
    protected $telnet_timeout = 10;
    protected static $instances = [];
    public function __construct($configPath)
    {
        $this->configPath = $configPath;
    }


    public function setTelnetPort($port) {
        $this->telnetPort = $port;
        return $this;
    }
    public function setSnmpTimeoutSec($snmp_timeout_sec) {
        $this->snmp_timeout_sec = $snmp_timeout_sec;
        return $this;
    }
    public function setSnmpRepeats($snmp_repeats) {
        $this->snmp_repeats = $snmp_repeats;
        return $this;
    }
    public function setTelnetTimeoutSec($telnet_timeout) {
        $this->telnet_timeout = $telnet_timeout;
        return $this;
    }
    public function setMikrotikApiPort($port) {
        $this->mikrotikApi = $port;
        return $this;
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


    private function initWalker($ip, $community) {
        return (new  MultiWalker())->addDevice(
            Device::init(
                $ip,
                $community,
                $this->snmp_timeout_sec,
                $this->snmp_repeats
            )
        );
    }
    private function initTelnet($ip, $login,$password) {
        return (new \SwitcherCore\Switcher\Objects\TelnetLazyConnect($ip, $this->getTelnetPort(), $this->telnet_timeout))
            ->login($login, $password);
    }
    private function initMikrotikApi($ip, $login,$password) {
        return (new \SwitcherCore\Switcher\Objects\RouterOsLazyConnect())
            ->setPort($this->getMikrotikApiPort())
            ->connect($ip, $login, $password);
    }
}