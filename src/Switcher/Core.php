<?php

namespace SwitcherCore\Switcher;

use SwitcherCore\Switcher\Objects\Telnet;
use SnmpWrapper\Walker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleNotFoundException;


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
     * @var Telnet
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

    /**
     * @return $this
     * @throws ModuleNotFoundException
     * @throws \ErrorException
     * @throws \SwitcherCore\Exceptions\ModuleErrorLoadException
     */
    function detectModel() {
        if(!$this->walker) {
            throw new \Exception("Snmp walker not setted. You must set walker before connect");
        }
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
        $hardware = "";
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
        if($descr || $objId || $hardware) {
            $this->model = $this->modelCollector->getModelByDetect($descr,$hardware,$objId);
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
                $moduleParams = $this->moduleCollector->getByName($moduleName);
                foreach ($moduleParams->getDependencyModules() as $name) {
                    print_r($name);

                    if(key_exists($name, $modules)) {
                        $module->setDependencyModule($name, $modules[$name]);
                    } else {
                        $module->setDependencyModule($name, null);
                    }
                }
            }

        } else {
            throw new \Exception("Returned empty response from walker, it's problem");
        }
        return $this;
    }
    function getModule($moduleName) {
        if(!$this->model) {
            throw new \Exception("Device properties and oids not loaded. Are you use ::connect() first?");
        }
        if(isset($this->model->getModules()[$moduleName])) {
            return $this->model->getModules()[$moduleName];
        } else {
            throw new ModuleNotFoundException("Module $moduleName not found for model {$this->model->getName()}");
        }
    }

    function action($moduleName, $arguments = []) {
        $moduleParams = $this->moduleCollector->getByName($moduleName);
        $moduleParams->validate($arguments);
        return $this->getModule($moduleName)->walk($arguments)->getPrettyFiltered($arguments);
    }

}
