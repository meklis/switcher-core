<?php

namespace SwitcherCore\Switcher;

use DI\Container;
use DI\ContainerBuilder;
use ErrorException;
use Exception;
use meklis\network\Telnet;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use RouterosAPI;
use SnmpWrapper\MultiWalkerInterface;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SnmpWrapper\Oid as O;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleErrorLoadException;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\RouterOsLazyConnect;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;
use function DI\autowire;


class Core
{

    /**
     * @var Container
     */
    protected $container;


    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Device
     */
    protected $device;
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param CacheInterface $cache
     * @return $this
     * @throws \DI\DependencyException
     */

    public function setCache(CacheInterface $cache)
    {
        $this->container->injectOn($cache);
        $this->cache = $cache;
        return $this;
    }


    public function setLogger(Logger $logger)
    {
        $this->container->injectOn($logger);
        $this->logger = $logger;
        return $this;
    }

    function __construct(Reader $reader, ?CacheInterface $cache = null, ?Logger $logger = null)
    {
        $container = $this->buildContainer();

        if($logger === null) {
            $logger = new \Monolog\Logger('switcher-core');
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);
            $handler = new NullHandler();
            $logger->pushHandler($handler);
        }
        $this->logger = $logger;
        $container->set(Reader::class, $reader);
        $container->set(OidCollector::class, function (ContainerInterface $c) {
            return OidCollector::init($c->get(Reader::class));
        });
        $container->set(ModelCollector::class, function (ContainerInterface $c) {
            return ModelCollector::init($c->get(Reader::class));
        });
        $container->set(ModuleCollector::class, function (ContainerInterface $c) {
            return ModuleCollector::init($c->get(Reader::class));
        });
        if ($cache !== null) {
            $this->cache = $cache;
            $container->set(CacheInterface::class, $cache);
        }
        $this->container = $container;
    }


    private function buildContainer()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->injectOn($this);
        return $container;
    }

    function setDevice(Device $device)
    {

        $this->logger->info("Device setted", [
            $device->getObject()
        ]);
        $this->device = $device;
        $this->container->injectOn($device);
        return $this;
    }

    function addInput($input)
    {
        if ($input instanceof MultiWalkerInterface) {
            $this->logger->info("Added walker interface - " . get_class($input));
            $this->container->set(MultiWalkerInterface::class, $input);
        } elseif ($input instanceof TelnetLazyConnect) {
            if (!$this->container->has(Model::class)) {
                $this->logger->info("Error: Model not setted. You must call init() first");
                throw new Exception("Model not setted. You must call init() first");
            }
            $this->logger->info("Added telnet interface - " . get_class($input));
            $model = $this->container->get(Model::class);
            $input->setHostType($model->getTelnetConnType());
            try {
                foreach ($model->getExtraParamByName('telnet_commands_after_connect') as $comm) {
                    $input->addCommandAfterLogin($comm);
                }
            } catch (Exception $e) {
            }
            $this->container->set(TelnetLazyConnect::class, $input);
        } elseif ($input instanceof RouterosAPI) {
            if (!$this->container->has(Model::class)) {
                throw new Exception("Model not setted. You must call init() first");
            }
            $this->container->set(RouterosAPI::class, $input);
        } else {
            throw new Exception("Unknown type of input, not supported");
        }
        return $this;
    }


    /**
     * @return array
     * @throws Exception
     */
    private function getDetectDevInfo()
    {
        $collector = $this->container->get(OidCollector::class);
        if($this->cache && $resp = $this->cache->get("SW_CORE_MODEL_DETECT:{$this->device->getIp()}")) {
            $this->logger->info("Returned detecting from cache", $resp );
            return  $resp;
        }
        $multiwalker = $this->container->get(MultiWalkerInterface::class);
        $response = $multiwalker->get([O::init($collector->getOidByName('sys.Descr')->getOid() . '.0')], 1, 1);
        if($response[0]->error) {
            throw new \SNMPException($response[0]->error);
        }
        $response = array_merge($response, $multiwalker->get([O::init($collector->getOidByName('sys.ObjId')->getOid() . '.0')], 1, 1));
        $response = array_merge($response, $multiwalker->get([O::init($collector->getOidByName('sys.IfacesCount')->getOid() . '.0')], 1, 1));

        $descr = "";
        $objId = "";
        $ifacesCount = 0;
        foreach ($response as $resp) {
            if ($resp->error) {
                $this->logger->error("Walker returned error: {$resp->error}");
                throw new \SNMPException($resp->error);
            } else {
                if ($collector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.Descr') {
                    $descr = $resp->getResponse()[0]->getValue();
                }
                if ($collector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.ObjId') {
                    $objId = $resp->getResponse()[0]->getValue();
                }
                if ($collector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.IfacesCount') {
                    $ifacesCount = $resp->getResponse()[0]->getValue();
                }
            }
        }
        if ($descr || $objId) {
            $this->logger->info("Detected model by descr and objId", [$descr, $objId]);
            if($this->cache) {
                $this->cache->set("SW_CORE_MODEL_DETECT:{$this->device->getIp()}",  [
                    'descr' => $descr,
                    'objid' => $objId,
                    'ifacesCount' => $ifacesCount,
                ], 600);
            }
            return [
                'descr' => $descr,
                'objid' => $objId,
                'ifacesCount' => $ifacesCount,
            ];
        } else {
            $this->logger->error("Returned empty response for model Detect");
            throw new Exception("Returned empty response from walker in detect model");
        }
    }

    function getNeedInputs()
    {
        return $this->container->get(Model::class)->getInputs();
    }


    /**
     * @return $this
     * @throws ModuleNotFoundException
     * @throws ErrorException
     * @throws ModuleErrorLoadException | Exception
     */
    function init()
    {

        if (!$this->container->has(MultiWalkerInterface::class)) {
            throw new Exception("Snmp walker not setted. You must set walker before connect");
        }
        $modelCollector = $this->container->get(ModelCollector::class);
        $oidCollector = $this->container->get(OidCollector::class);
        $devInfo = $this->getDetectDevInfo();
        $model = $modelCollector->getModelByDetect($devInfo['descr'], $devInfo['objid'], $devInfo['ifacesCount']);
        $this->container->set(Model::class, $model);
        $oidCollector->readEnterpriceOids($model);
        $this->declareModules($model);
        return $this;
    }

    function declareModules(Model $model)
    {
        foreach ($model->getModulesListAssoc() as $module => $object) {
            $this->logger->info("Declare module.{$module} with $object to DI");
            if (!class_exists($object)) {
                $this->logger->critical("ModuleClass {$object} for $module not found");
                throw new ModuleErrorLoadException("Module with name '$module' not found by ClassName {$object}");
            }
            $this->container->set("module.{$module}", autowire($object)->lazy());
        }
    }


    /**
     * @param $moduleName
     * @param array $arguments
     * @return mixed
     * @throws ModuleNotFoundException | Exception
     */
    public function action($moduleName, $arguments = [])
    {
        if (!$this->container->has("module.{$moduleName}")) {
            $this->logger->critical("Module $moduleName not found");
            throw new ModuleNotFoundException("Module with name $moduleName not found");
        }
        /**
         * @var AbstractModule
         */
        $module = $this->container->get("module.{$moduleName}");
        $this->container->get(ModuleCollector::class)->getByName($moduleName)->validate($arguments);
        return $module->run($arguments)->getPrettyFiltered($arguments);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getModulesData()
    {
        $modules = [];
        $model = $this->container->get(Model::class);
        $moduleCollector = $this->container->get(ModuleCollector::class);
        foreach ($model->getModulesList() as $moduleName) {
            $moduleConfig = $moduleCollector->getByName($moduleName);
            $modules[] = [
                'name' => $moduleConfig->getName(),
                'arguments' => $moduleConfig->getArguments(),
                'class' => $model->getModulesListAssoc()[$moduleName],
            ];
        }
        return $modules;
    }


    public function getDeviceMetaData()
    {
        $model = $this->container->get(Model::class);
        $meta = [
            'ports' => $model->getPorts(),
            'name' => $model->getName(),
            'extra' => $model->getExtra(),
            'detect' => $model->getDetect(),
            'modules' => $model->getModulesList(),
        ];
        $meta['connections'] = [
            'mikrotik_api' => false,
            'snmp' => false,
            'telnet' => false,
        ];
        if ($this->container->has(MultiWalkerInterface::class)) {
            $meta['connections']['snmp'] = true;
        }
        if ($this->container->has(Telnet::class)) {
            $meta['connections']['telnet'] = true;
        }
        if ($this->container->has(RouterosAPI::class)) {
            $meta['connections']['mikrotik_api'] = true;
        }
        return $meta;
    }

    public function getContainer() {
        return $this->container;
    }
}
