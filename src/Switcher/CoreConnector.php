<?php


namespace SwitcherCore\Switcher;


use ErrorException;
use Exception;
use Psr\Container\ContainerInterface;
use SnmpWrapper\Device;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\NoProxy\MultiWalker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleErrorLoadException;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Switcher\Objects\RouterOsLazyConnect;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;

class CoreConnector
{
    protected  $configPath;
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var MultiWalkerInterface
     */
    protected $walker;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;


    /**
     * @var \SwitcherCore\Config\Collector|ModuleCollector
     */
    protected $moduleCollector;

    /**
     * @var \SwitcherCore\Config\Collector|ModelCollector
     */
    protected $modelCollector;

    /**
     * @var \SwitcherCore\Config\Collector|OidCollector
     */
    protected $oidCollector;

    /**
     * @var Core[]
     */
    protected $instances = [];
    public function __construct($configPath)
    {
        $this->walker = new MultiWalker();
        $reader = new Reader($configPath);
        $this->oidCollector = OidCollector::init($reader);
        $this->modelCollector = ModelCollector::init($reader);
        $this->moduleCollector = ModuleCollector::init($reader);
    }

    public function setWalker(MultiWalkerInterface $walker) {
        $this->walker = $walker;
        return $this;
    }

    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
        return $this;
    }
    public function setLogger(\Monolog\Logger $logger) {
        $this->logger = $logger;
        return $this;
    }
    public function getAllCoreInstances() {
        return $this->instances;
    }

    public function closeAllCoreInstances() {
        $this->instances = [];
        return $this;
    }


    /**
     * @param $ip
     * @param $community
     * @param $login
     * @param $password
     * @return Core
     * @throws ErrorException
     * @throws ModuleErrorLoadException
     * @throws ModuleNotFoundException
     * @throws Exception
     */
    public function getOrInit(\SwitcherCore\Switcher\Device  $device) {
        if(isset($this->instances[$device->getIp()])) {
            return $this->instances[$device->getIp()];
        }
        return $this->init($device);
    }

    /**
     * @param $ip
     * @return bool
     */
    public function isConnected($ip) {
        return isset($this->instances[$ip]);
    }

    /**
     * @param $ip
     * @return Core
     * @throws Exception
     */
    public function get($ip) {
        if(self::isConnected($ip)) {
            return $this->instances[$ip];
        }
        throw new Exception("Connection to $ip not existed");
    }

    public function init(\SwitcherCore\Switcher\Device  $device) {

        $walker = $this->initWalker($device);

        $core = (new Core)->setDevice($device)
            ->addInput($walker)
            ->setModelCollector($this->modelCollector)
            ->setModuleCollector($this->moduleCollector)
            ->setOidCollector($this->oidCollector);

        if($this->cache) {
            $core->setCache($this->cache);
        }
        if($this->logger) {
            $core->setLogger($this->logger);
        }

        $core->init();
        $inputs_list = $core->getNeedInputs();
        if(in_array('telnet', $inputs_list)) {
            $telnet = $this->initTelnet($device);
            $core->addInput($telnet);
        }

        if(in_array('routeros_api', $inputs_list)) {
            $routerOS = $this->initMikrotikApi($device);
            $core->addInput($routerOS);
        }
        $this->instances[$device->getIp()] = $core;
        return $core;
    }


    private function initWalker(\SwitcherCore\Switcher\Device $device) {
        $walker = clone $this->walker;
        return $walker->addDevice(
            Device::init(
                $device->getIp(),
                $device->getCommunity(),
                $device->snmpTimeoutSec,
                $device->snmpRepeats
            )
        );
    }
    private function initTelnet(\SwitcherCore\Switcher\Device $device) {
        return (new TelnetLazyConnect($device->getIp(), $device->telnetPort , $device->telnetTimeout, 15))
            ->login($device->getLogin(), $device->getPassword());
    }
    private function initMikrotikApi(\SwitcherCore\Switcher\Device $device) {
        return (new RouterOsLazyConnect())
            ->setPort($device->mikrotikApiPort)
            ->connect($device->getIp(), $device->getLogin(), $device->getPassword());
    }
}
