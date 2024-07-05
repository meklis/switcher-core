<?php

namespace SwitcherCore\Switcher;

use DI\Container;
use DI\ContainerBuilder;
use ErrorException;
use Exception;
use Meklis\Network\Console\Helpers\Helpers;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use RouterosAPI;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid as O;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\TrapCollector;
use SwitcherCore\Exceptions\ModuleErrorLoadException;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;
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
        $this->container->set(CacheInterface::class, $cache);
        $this->cache = $cache;
        return $this;
    }


    public function setLogger(Logger $logger)
    {
        $this->container->set(Logger::class, $logger);
        $this->logger = $logger;
        return $this;
    }

    function setOidCollector(OidCollector $collector)
    {
        $this->container->set(OidCollector::class, $collector);
        return $this;
    }

    function setTrapCollector(TrapCollector $collector)
    {
        $this->container->set(TrapCollector::class, $collector);
        return $this;
    }

    function setModuleCollector(ModuleCollector $collector)
    {
        $this->container->set(ModuleCollector::class, $collector);
        return $this;
    }

    function setModelCollector(ModelCollector $collector)
    {
        $this->container->set(ModelCollector::class, $collector);
        return $this;
    }

    function __construct(?CacheInterface $cache = null, ?Logger $logger = null)
    {
        $container = $this->buildContainer();

        $logger = new \Monolog\Logger('switcher-core');
        $processor = new UidProcessor();
        $logger->pushProcessor($processor);
        $handler = new NullHandler();
        $logger->pushHandler($handler);
        $this->logger = $logger;
        $container->set(Logger::class, $logger);
        if ($cache !== null) {
            $this->cache = $cache;
            $container->set(CacheInterface::class, $cache);
        }

        $this->container = $container;
    }


    protected function buildContainer()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->set(ContainerInterface::class, $this);
        return $container;
    }

    function setDevice(Device $device)
    {
        $this->logger->info("Device setted", [
            $device->getObject()
        ]);
        $this->device = $device;
        $this->container->set(Device::class, $device);
        return $this;
    }

    function addInput($input)
    {
        if ($input instanceof MultiWalkerInterface) {
            $this->logger->info("Added walker interface - " . get_class($input));
            $this->container->set(MultiWalkerInterface::class, $input);
        } elseif ($input instanceof ConsoleInterface) {
            if (!$this->container->has(Model::class)) {
                $this->logger->info("Error: Model not setted. You must call init() first");
                throw new Exception("Model not setted. You must call init() first");
            }
            $this->logger->info("Added console interface - " . get_class($input));
            $model = $this->container->get(Model::class);
            $helper = Helpers::getByName($model->getConsoleConnType());
            $input->setDeviceHelper($helper);
            try {
                if ($commands = $model->getExtraParamByName('console_commands_after_connect')) {
                    $helper->setAfterLoginCommands($commands);
                }

            } catch (Exception $e) {
            }
            $this->container->set(ConsoleInterface::class, $input);
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
    protected function getDetectDevInfo()
    {
        $collector = $this->container->get(OidCollector::class);
        $multiwalker = $this->container->get(MultiWalkerInterface::class);

        //Check device is alive before getting info from cache
        $response = $multiwalker->get([O::init($collector->getOidByName('sys.Descr')->getOid() . '.0')], 1, 1);
        if ($response[0]->error) {
            if (strpos($response[0]->error, 'No Such Object available on this agent at this OID') !== false) {
                throw new \ErrorException("Current device not support detect. Please, set device model manually");
            }
            throw new \SNMPException($response[0]->error);
        }
        if ($this->cache && $resp = $this->cache->get("SW_CORE_MODEL_DETECT:{$this->device->getIp()}")) {
            $this->logger->info("Returned detecting from cache", $resp);
            return $resp;
        }
        $response = array_merge($response, $multiwalker->get([O::init($collector->getOidByName('sys.ObjId')->getOid() . '.0')], 2, 2));
        $response = array_merge($response, $multiwalker->get([O::init($collector->getOidByName('sys.IfacesCount')->getOid() . '.0')], 2, 2));
        $descr = "";
        $objId = "";
        $ifacesCount = 0;
        foreach ($response as $resp) {
            if ($resp->error) {
                $this->logger->error("Walker returned error: {$resp->error}");
                continue;
            }
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
        if ($descr || $objId) {
            $this->logger->info("Detected model by descr and objId", [$descr, $objId]);
            if ($this->cache) {
                $this->cache->set("SW_CORE_MODEL_DETECT:{$this->device->getIp()}", [
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
        $this->logger->info("Start initialization Core for device {$this->device->getIp()}");
        if (!$this->container->has(MultiWalkerInterface::class)) {
            throw new Exception("Snmp walker not setted. You must set walker before connect");
        }
        /**
         * @var ModelCollector $modelCollector
         */
        $modelCollector = $this->container->get(ModelCollector::class);
        $oidCollector = $this->container->get(OidCollector::class);

        /**
         * @var TrapCollector
         */
        $trapCollector = $this->container->get(TrapCollector::class);

        /**
         * @var $multiwalker MultiWalkerInterface
         */
        $multiwalker = $this->container->get(MultiWalkerInterface::class);
        if ($mk = $this->device->getModelKey()) {
            //Check device is alive before getting info from cache
            if ($this->device->isCheckAlive()) {
                $response = $multiwalker->get([O::init($oidCollector->getOidByName('sys.Descr')->getOid() . '.0')], 1, 1);

                if ($response[0]->error && strpos($response[0]->error, 'No response from') !== false) {
                    throw new \SNMPException($response[0]->error);
                }
            }
            $model = $modelCollector->getModelByKey($mk);
        } else {
            $devInfo = $this->getDetectDevInfo();
            $model = $modelCollector->getModelByDetect($devInfo['descr'], $devInfo['objid'], $devInfo['ifacesCount']);
            if ($model->getRewrites()) {
                $response = $multiwalker->get([O::init($model->getRewrites()['oid'])], 3, 3);
                if ($response[0]->error) {
                    throw new \SNMPException("Error rewrites detect - " . $response[0]->error->getMessage());
                }
                $model->rewriteModelByValue($response[0]->getResponse()[0]->getValue());
            }
        }
        $this->container->set(Model::class, $model);
        $oidCollector->readEnterpriceOids($model);
        $trapCollector->readEnterpriceTraps($model);
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
         * @var $module AbstractModule
         */
        $module = $this->container->get("module.{$moduleName}");


        $this->container->get(ModuleCollector::class)->getByName($moduleName)->validate($arguments);

        try {
            $this->logger->info("Run module {$moduleName}", [
                'arguments' => $arguments,
                'module' => $moduleName,
            ]);
            $data = $module->run($arguments)->getPrettyFiltered($arguments);
        } catch (\Throwable $e) {
            $this->logger->error("error execute module {$moduleName} - {$e->getMessage()}", [
                'arguments' => $arguments,
                'module' => $moduleName,
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'trace' => $e->getTraceAsString(),
                ]
            ]);
            throw $e;
        }
        return $data;
    }

    /**
     * @param $object
     * @param $data
     * @return array
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \SwitcherCore\Exceptions\TrapDeclarationNotFoundByObject
     */
    public function trap($object, $data = [])
    {
        $trap = $this->container->get(TrapCollector::class)->findTrapById($object);
        $oidCollector = $this->container->get(OidCollector::class);

        $response = [
            'declaration' => [
                'modules' => $trap->getModules(),
                'name' => $trap->getName(),
                'object' => $trap->getObject(),
                'description' => $trap->getDescription(),
                'is_interface' => $trap->isInterface(),
            ],
            'errors' => [],
            'modules' => [],
            'parsed' => [],
            'interface' => null,
        ];
        foreach ($data as $oid=>$value) {
            try {
                $finded = $oidCollector->findOidById($oid);
                $parsedValue = isset($finded->getValues()[$value['value']]) ? $finded->getValues()[$value['value']] : $value['value'];
                $response['parsed'][$oid] = [
                    'type' => $value['type'],
                    'value' => $value['value'],
                    'hex' => isset($value['hex']) ? $value['hex'] : null,
                    'name' => $finded->getName(),
                    'parsed_value' => $parsedValue,
                ];
            } catch (\Throwable $e) {
                $response['errors'][] = "error working with trap {$object} for parsing $oid - {$e->getMessage()}";
                $this->logger->error("error working with trap {$object} - {$e->getMessage()}");
            }
        }
        if($trap->isInterface()) {
            foreach ($data as $oid=>$value) {
                try {
                    /**
                     * @var $module AbstractModule
                     */
                    $module = $this->container->get("module.parse_interface");
                    $response['interface'] = $module->run(['interface' => Helper::getIndexByOid($oid)])->getPrettyFiltered(['interface' => Helper::getIndexByOid($oid)]);
                } catch (\Throwable $e) {
                    $response['errors'][] = "not found interface on trap {$object}";
                    $this->logger->error("not found interface on trap {$object}");
                }
            }
        }
        foreach ($trap->getModules() as $moduleName) {
            try {
                /**
                 * @var $module AbstractModule
                 */
                $module = $this->container->get("module.{$moduleName}");
                $response['modules'][$moduleName] = $module->trap( $trap, [
                    'parsed' =>  $response['parsed'],
                    'raw' =>  $data,
                    'interface' => $response['interface'],
                ]);
            } catch (\Throwable $e) {
                $response['errors'][] = "error working with trap {$object} for calling module $moduleName - {$e->getMessage()}";
                $this->logger->error("error working with trap {$object} - {$e->getMessage()}");
            }
        }

        return $response;
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

    /**
     * @return array
     * @throws Exception
     */
    public function isModuleExist($module)
    {
        $model = $this->container->get(Model::class);
        foreach ($model->getModulesList() as $moduleName) {
            if ($moduleName === $module) {
                return true;
            }
        }
        return false;
    }

    public function getDeviceMetaData()
    {
        $model = $this->container->get(Model::class);
        $meta = [
            'ports' => $model->getPorts(),
            'name' => $model->getName(),
            'key' => $model->getKey(),
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

    public function getContainer()
    {
        return $this->container;
    }
}
