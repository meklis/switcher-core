<?php

namespace SwitcherCore\Switcher;

use Meklis\TelnetOverProxy\Telnet;
use SnmpWrapper\Walker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;


class Core
{
    /**
     * @var string
     */
    protected  $ip;
    /**
     * @var string
     */
    protected  $community;
    /**
     * @var ModelCollector
     */
    protected  $modelCollector;
    /**
     * @var Walker
     */
    protected  $walker;
    /**
     * @var TelnetLazyConnect
     */
    protected  $telnet;
    /**
     * @var OidCollector
     */
    protected   $oidCollector;

    /**
     * @var ModuleCollector
     */
    protected  $moduleCollector;

    /**
     * @var Model
     */

    protected $model;
    function __construct(Reader $reader)
    {
        $this->oidCollector = \SwitcherCore\Config\OidCollector::init($reader);
        $this->modelCollector = \SwitcherCore\Config\ModelCollector::init($reader);
        $this->moduleCollector = \SwitcherCore\Config\ModuleCollector::init($reader);

    }
    function setWalker(Walker $walker) {
        $this->walker = $walker;
        return $this;
    }
    function setTelnet(Telnet $telnet) {
        $this->telnet = $telnet;
        return $this;
    }

    private function getDetectDevInfo() {
        $prev_state = $this->walker->getCacheStatus();
        $response = $this->walker
            ->useCache('true')
            ->walk([
                $this->oidCollector->getOidByName('sys.Descr')->getOid(),
                $this->oidCollector->getOidByName('sys.ObjId')->getOid(),
            ]);
        $this->walker->useCache($prev_state);
        $descr = "";
        $objId = "";
        foreach ($response as $resp) {
            if($resp->error) {
                throw new \Exception("Walker returned error: {$resp->error}");
            } else {
                if($this->oidCollector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.Descr') {
                    $descr = $resp->getResponse()[0]->getValue();
                }
                if($this->oidCollector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.ObjId') {
                    $objId = $resp->getResponse()[0]->getValue();
                }
            }
        }
        if($descr || $objId) {
            return [
                'descr' => $descr,
                'objid' => $objId,
            ];
        } else {
            throw new \Exception("Returned empty response from walker in detect model");
        }
    }
    /**
     * @return $this
     * @throws ModuleNotFoundException
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException
     */
    function init() {
        if(!$this->walker) {
            throw new \Exception("Snmp walker not setted. You must set walker before connect");
        }
        $devInfo = $this->getDetectDevInfo();
        $this->model = $this->modelCollector->getModelByDetect($devInfo['descr'],$devInfo['objid']);

        $this->oidCollector->readEnterpriceOids($this->model);
        $this->model->loadModules();

        if($this->telnet) {
            $this->telnet->setHostType($this->model->getTelnetConnType());
        }

        //Inject objects to modules
        $modules = [];
        foreach ($this->model->getModules() as $moduleName=>$module) {
            $modules[$moduleName] = $module;
            $this->model->setModule(
                $moduleName,
                $module->setModel($this->model)
                    ->setOidCollector($this->oidCollector)
                    ->setWalker($this->walker)
                    ->setTelnetConn($this->telnet)

            );
        }
        foreach ($modules as $moduleName=>$module) {
            foreach ($this->moduleCollector->getByName($moduleName)->getDependencyModules() as $name) {
                if(isset($modules[$name])) {
                    $module->setDependencyModule($name, $modules[$name]);
                }
            }
        }

        return $this;
    }
    public function getModule($moduleName) {
        if(!$this->model) {
            throw new \Exception("Device properties and oids not loaded. Are you use ::connect() first?");
        }
        if(isset($this->model->getModules()[$moduleName])) {
            return $this->model->getModules()[$moduleName];
        } else {
            throw new ModuleNotFoundException("Module $moduleName not found for model {$this->model->getName()}");
        }
    }

    public function action($moduleName, $arguments = []) {
        $moduleParams = $this->moduleCollector->getByName($moduleName);
        $moduleParams->validate($arguments);
        return $this->getModule($moduleName)->run($arguments)->getPrettyFiltered($arguments);
    }

    public function getModulesData() {
        $modules = [];
        foreach ($this->model->getModulesList() as $moduleName) {
            $moduleConfig = $this->moduleCollector->getByName($moduleName);
            $modules[] = [
                'name' => $moduleConfig->getName(),
                'depends' => $moduleConfig->getDependencyModules(),
                'arguments' => $moduleConfig->getArguments(),
                'class' => get_class($this->model->getModules()[$moduleName]),
            ];
        }
        return $modules;
    }

    public function getDeviceMetaData() {
        $meta = [
            'ports' => $this->model->getPorts(),
            'name' => $this->model->getName(),
            'extra' => $this->model->getExtra(),
            'detect' => $this->model->getDetect(),
            'modules' => $this->model->getModulesList(),
        ];
        $meta['connections'] = [
          'mikrotik_api' => false,
          'snmp' => false,
          'telnet' => false,
        ];
        if($this->walker) {
            $meta['connections']['snmp'] = true;
        }
        if($this->telnet) {
            $meta['connections']['telnet'] = true;
        }
        return $meta;
    }
}
