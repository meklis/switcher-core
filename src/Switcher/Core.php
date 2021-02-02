<?php

namespace SwitcherCore\Switcher;

use DI\Container;
use meklis\network\Telnet;
use Psr\Container\ContainerInterface;
use SnmpWrapper\NoProxy\MultiWalker;
use SnmpWrapper\MultiWalkerInterface;
use SwitcherCore\Config\Collector;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SnmpWrapper\Oid as O;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleErrorLoadException;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Modules\AbstractModule;


class Core
{

    /**
     * @var Container
     */
    protected $container;


    public function setCache(CacheInterface $cache)
    {
        $this->container->injectOn($cache);
        return $this;
    }

    function __construct(Reader $reader, ?CacheInterface $cache = null)
    {
        $container = $this->buildContainer();
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
            $container->set(CacheInterface::class, $cache);
        }
        $this->container = $container;
    }

    private function buildContainer()
    {
        $builder = new \DI\ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(true);
        $container = $builder->build();
        $container->injectOn($this);
        return $container;
    }

    function setDevice(Device $device)
    {
        $this->container->injectOn($device);
        return $this;
    }

    function addInput($input)
    {
        if ($input instanceof MultiWalkerInterface) {
            $this->container->set(MultiWalkerInterface::class, $input);
        } elseif ($input instanceof \meklis\network\Telnet) {
            if (!$this->container->has(Model::class)) {
                throw new \Exception("Model not setted. You must call init() first");
            }
            $model = $this->container->get(Model::class);
            $input->setHostType($model->getTelnetConnType());
            try {
                foreach ($model->getExtraParamByName('telnet_commands_after_connect') as $comm) {
                    $input->addCommandAfterLogin($comm);
                }
            } catch (\Exception $e) {
            }
            $this->container->set(Telnet::class, $input);
        } elseif ($input instanceof \RouterosAPI) {
            if (!$this->container->has(Model::class)) {
                throw new \Exception("Model not setted. You must call init() first");
            }
            $this->container->set(\RouterosAPI::class, $input);
        } else {
            throw new \Exception("Unknown type of input, not supported");
        }
        return $this;
    }


    /**
     * @return array
     * @throws \Exception
     */
    private function getDetectDevInfo()
    {
        $collector = $this->container->get(OidCollector::class);
        $response = $this->container->get(MultiWalkerInterface::class)
            ->walk([
                O::init($collector->getOidByName('sys.Descr')->getOid()),
                O::init($collector->getOidByName('sys.ObjId')->getOid()),
            ]);
        $descr = "";
        $objId = "";
        foreach ($response as $resp) {
            if ($resp->error) {
                throw new \Exception("Walker returned error: {$resp->error}");
            } else {
                if ($collector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.Descr') {
                    $descr = $resp->getResponse()[0]->getValue();
                }
                if ($collector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.ObjId') {
                    $objId = $resp->getResponse()[0]->getValue();
                }
            }
        }
        if ($descr || $objId) {
            return [
                'descr' => $descr,
                'objid' => $objId,
            ];
        } else {
            throw new \Exception("Returned empty response from walker in detect model");
        }
    }

    function getNeedInputs()
    {
        return $this->container->get(Model::class)->getInputs();
    }


    /**
     * @return $this
     * @throws ModuleNotFoundException
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException | \Exception
     */
    function init()
    {

        if (!$this->container->has(MultiWalkerInterface::class)) {
            throw new \Exception("Snmp walker not setted. You must set walker before connect");
        }
        $modelCollector = $this->container->get(ModelCollector::class);
        $oidCollector = $this->container->get(OidCollector::class);
        $devInfo = $this->getDetectDevInfo();
        $model = $modelCollector->getModelByDetect($devInfo['descr'], $devInfo['objid']);
        $this->container->set(Model::class, $model);
        $oidCollector->readEnterpriceOids($model);
        $this->declareModules($model);
        return $this;
    }

    function declareModules(Model $model)
    {
        foreach ($model->getModulesListAssoc() as $module => $object) {

            if (!class_exists($object)) {
                throw new ModuleErrorLoadException("Module with name '$module' not found by ClassName {$object}");
            }
            echo "$module = $object\n";
            $this->container->set("module.{$module}", \DI\autowire($object)->lazy());
        }
    }


    /**
     * @param $moduleName
     * @param array $arguments
     * @return mixed
     * @throws ModuleNotFoundException | \Exception
     */
    public function action($moduleName, $arguments = [])
    {
        if (!$this->container->has("module.{$moduleName}")) {
            throw new ModuleNotFoundException("Module with name $moduleName not found");
        }
        /**
         * @var AbstractModule
         */
        $module = $this->container->get("module.{$moduleName}");
        echo "$module\n";
        $this->container->get(ModuleCollector::class)->getByName($moduleName)->validate($arguments);
        return $module->run($arguments)->getPrettyFiltered($arguments);
    }

    /**
     * @return array
     * @throws \Exception
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
        if ($this->container->has(\RouterosAPI::class)) {
            $meta['connections']['mikrotik_api'] = true;
        }
        return $meta;
    }

    public function getContainer() {
        return $this->container;
    }
}
