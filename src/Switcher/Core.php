<?php

namespace SwitcherCore\Switcher;

use SnmpWrapper\NoProxy\MultiWalker;
use SnmpWrapper\MultiWalkerInterface;
use SwitcherCore\Config\Objects\Oid;
use SnmpWrapper\Oid as O;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleNotFoundException;
use SwitcherCore\Switcher\Objects\InputsStore;
use SwitcherCore\Switcher\Objects\ModuleStore;


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
     * @var InputsStore
     */

    protected $objects;

    /**
     * @var ModuleStore
     */
    protected $modules;


    function __construct(Reader $reader)
    {
        $this->objects = new InputsStore;
        $this->objects->oidCollector = \SwitcherCore\Config\OidCollector::init($reader);
        $this->objects->modelCollector = \SwitcherCore\Config\ModelCollector::init($reader);
        $this->objects->moduleCollector = \SwitcherCore\Config\ModuleCollector::init($reader);

        $this->modules = new ModuleStore;
    }

    function addInput($input) {
        if($input instanceof MultiWalkerInterface) {
            $this->objects->walker = $input;
        } elseif ($input instanceof \meklis\network\Telnet) {
            if(!$this->objects->isExist('model')) {
                throw new \Exception("Model not detected. You must call init() first");
            }
            $input->setHostType($this->objects->model->getTelnetConnType());
            $this->objects->telnet = $input;
        } elseif ($input instanceof \RouterosAPI) {
            if(!$this->objects->isExist('model')) {
                throw new \Exception("Model not detected. You must call init() first");
            }
            $this->objects->routerOsApi = $input;
        } else {
            throw new \Exception("Unknown type of input, not supported");
        }
        return $this;
    }


    /**
     * @return array
     * @throws \Exception
     */
    private function getDetectDevInfo() {
        $response = $this->objects->walker
            ->walk([
                O::init($this->objects->oidCollector->getOidByName('sys.Descr')->getOid(), true),
                O::init($this->objects->oidCollector->getOidByName('sys.ObjId')->getOid(), true),
            ]);
        $descr = "";
        $objId = "";
        foreach ($response as $resp) {
            if($resp->error) {
                throw new \Exception("Walker returned error: {$resp->error}");
            } else {
                if($this->objects->oidCollector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.Descr') {
                    $descr = $resp->getResponse()[0]->getValue();
                }
                if($this->objects->oidCollector->findOidById($resp->getResponse()[0]->getOid())->getName() == 'sys.ObjId') {
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

    function getNeedInputs() {
        return $this->objects->model->getInputs();
    }

    /**
     * @return $this
     * @throws ModuleNotFoundException
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException | \Exception
     */
    function init() {
        if(!$this->objects->isExist('walker')) {
            throw new \Exception("Snmp walker not setted. You must set walker before connect");
        }
        $devInfo = $this->getDetectDevInfo();
        $this->objects->model = $this->objects->modelCollector->getModelByDetect($devInfo['descr'],$devInfo['objid']);

        $this->objects->oidCollector->readEnterpriceOids($this->objects->model);
        $this->objects->model->initModules();

        foreach ($this->objects->model->getModules() as $moduleName=>$module) {
            $this->modules->set($moduleName, $module);
            $module->setInputsStore($this->objects);
            $module->setModuleStore($this->modules);
        }
        return $this;
    }

    /**
     * @param $moduleName
     * @param array $arguments
     * @return mixed
     * @throws ModuleNotFoundException | \Exception
     */
    public function action($moduleName, $arguments = []) {
        $moduleParams = $this->objects->moduleCollector->getByName($moduleName);
        $moduleParams->validate($arguments);
        return $this->modules->get($moduleName)->run($arguments)->getPrettyFiltered($arguments);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getModulesData() {
        $modules = [];
        foreach ($this->objects->model->getModulesList() as $moduleName) {
            $moduleConfig = $this->objects->moduleCollector->getByName($moduleName);
            $modules[] = [
                'name' => $moduleConfig->getName(),
                'arguments' => $moduleConfig->getArguments(),
                'class' => get_class($this->objects->model->getModules()[$moduleName]),
            ];
        }
        return $modules;
    }

    public function getDeviceMetaData() {
        $meta = [
            'ports' => $this->objects->model->getPorts(),
            'name' => $this->objects->model->getName(),
            'extra' => $this->objects->model->getExtra(),
            'detect' => $this->objects->model->getDetect(),
            'modules' => $this->objects->model->getModulesList(),
        ];
        $meta['connections'] = [
          'mikrotik_api' => false,
          'snmp' => false,
          'telnet' => false,
        ];
        if($this->objects->isExist('snmp')) {
            $meta['connections']['snmp'] = true;
        }
        if($this->objects->isExist('telnet')) {
            $meta['connections']['telnet'] = true;
        }
        if($this->objects->isExist('routerOsApi')) {
            $meta['connections']['mikrotik_api'] = true;
        }
        return $meta;
    }
}
