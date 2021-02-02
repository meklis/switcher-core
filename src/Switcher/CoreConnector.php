<?php


namespace SwitcherCore\Switcher;


use SnmpWrapper\Device;
use SnmpWrapper\NoProxy\MultiWalker;
use SwitcherCore\Config\Reader;

class CoreConnector
{
    protected  $configPath;
    /**
     * @var CacheInterface
     */
    protected $cache;
    protected static $instances = [];
    public function __construct($configPath)
    {
        $this->configPath = $configPath;
    }
    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
        return $this;
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
    public function getOrInit(\SwitcherCore\Switcher\Device  $device) {
        if(isset(self::$instances[$device->getIp()])) {
            return self::$instances[$device->getIp()];
        }
        return $this->init($device);
    }

    /**
     * @param $ip
     * @return bool
     */
    public function isConnected($ip) {
        return isset(self::$instances[$ip]);
    }

    /**
     * @param $ip
     * @return Core
     * @throws \Exception
     */
    public function get($ip) {
        if(self::isConnected($ip)) {
            return self::$instances[$ip];
        }
        throw new \Exception("Connection to $ip not existed");
    }

    public function init(\SwitcherCore\Switcher\Device  $device) {

        $walker = $this->initWalker($device);

        $core = (new \SwitcherCore\Switcher\Core(
            new  Reader($this->configPath),
            $this->cache
        ))->setDevice($device)->addInput($walker)->init();
        $inputs_list = $core->getNeedInputs();
        if(in_array('telnet', $inputs_list)) {
            $telnet = $this->initTelnet($device);
            $core->addInput($telnet);
        }

        if(in_array('routeros_api', $inputs_list)) {
            $routerOS = $this->initMikrotikApi($device);
            $core->addInput($routerOS);
        }
        self::$instances[$device->getIp()] = $core;
        return $core;
    }


    private function initWalker(\SwitcherCore\Switcher\Device $device) {
        return (new  MultiWalker())->addDevice(
            Device::init(
                $device->getIp(),
                $device->getCommunity(),
                $device->snmpTimeoutSec,
                $device->snmpRepeats
            )
        );
    }
    private function initTelnet(\SwitcherCore\Switcher\Device $device) {
        return (new \SwitcherCore\Switcher\Objects\TelnetLazyConnect($device->getIp(), $device->telnetPort , $device->telnetTimeout))
            ->login($device->getLogin(), $device->getPassword());
    }
    private function initMikrotikApi(\SwitcherCore\Switcher\Device $device) {
        return (new \SwitcherCore\Switcher\Objects\RouterOsLazyConnect())
            ->setPort($device->mikrotikApiPort)
            ->connect($device->getIp(), $device->getLogin(), $device->getPassword());
    }
}