<?php


namespace SwitcherCore\Switcher;


use ErrorException;
use Exception;
use SnmpWrapper\Device;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\NoProxy\MultiWalker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleErrorLoadException;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Switcher\Console\SshLazyConnect;
use SwitcherCore\Switcher\Console\TelnetLazyConnect;
use SwitcherCore\Switcher\Objects\RouterOsLazyConnect;

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
    public function __construct($configPath, $buildCachePath = null)
    {
        if($buildCachePath) {
            if(file_exists($buildCachePath) && time() - filemtime($buildCachePath) < 300) {
                $obj = unserialize( file_get_contents($buildCachePath));
                $this->walker = $obj->walker;
                $this->oidCollector = $obj->oidCollector;
                $this->modelCollector = $obj->modelCollector;
                $this->moduleCollector = $obj->moduleCollector;
                return;
            }
        }
        $this->walker = new MultiWalker();
        $reader = new Reader($configPath);
        $this->oidCollector = OidCollector::init($reader);
        $this->modelCollector = ModelCollector::init($reader);
        $this->moduleCollector = ModuleCollector::init($reader);

        if($buildCachePath) {
            file_put_contents($buildCachePath, serialize($this));
        }
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
        if(in_array('console', $inputs_list)) {
            $console = $this->initConsole($device);
            $core->addInput($console);
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
        $port = $device->snmpPort ? $device->snmpPort : 161;
        $version = $device->snmpVersion ? $device->snmpVersion : '2c';
        return $walker->addDevice(
            Device::init(
                $device->getIp(),
                $device->getPublicCommunity(),
                $device->getPrivateCommunity(),
                $device->snmpTimeoutSec,
                $device->snmpRepeats,
            )->setPort($port)->setVersion($version)
        );
    }
    private function initConsole(\SwitcherCore\Switcher\Device $device) {
        $waitByteSec = $device->consoleWaitByteSec ?  $device->consoleWaitByteSec : 10;
        if(!$device->get('consoleConnectionType') || $device->get('consoleConnectionType') == 'telnet') {
            return (new TelnetLazyConnect($device->consoleTimeout, $waitByteSec))
                ->setHost($device->getIp(), $device->consolePort)
                ->setAccess($device->getLogin(), $device->getPassword());
        } else if ($device->get('consoleConnectionType') == 'ssh') {
            return (new SshLazyConnect($device->consoleTimeout, $waitByteSec))
                ->setHost($device->getIp(), $device->consolePort)
                ->setAccess($device->getLogin(), $device->getPassword());
        } else {
            throw new \Exception("Another console not implemented");
        }
    }
    private function initMikrotikApi(\SwitcherCore\Switcher\Device $device) {
        return (new RouterOsLazyConnect())
            ->setPort($device->mikrotikApiPort)
            ->connect($device->getIp(), $device->getLogin(), $device->getPassword());
    }
}
