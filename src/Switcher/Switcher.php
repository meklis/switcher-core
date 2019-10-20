<?php

namespace SwitcherCore\Switcher;

use SnmpWrapper\Walker;
use SwitcherCore\Config\CommandCollector;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Config\Reader;
use SwitcherCore\Exceptions\ModuleNotFoundException;


class Switcher
{
    /**
     * @var string
     */
    public $ip;
    /**
     * @var string
     */
    public $community;
    /**
     * @var ModelCollector
     */
    public $modelCollector;
    /**
     * @var Walker
     */
    public $walker;
    /**
     * @var OidCollector
     */
    public  $oidCollector;
    /**
     * @var CommandCollector
     */
    public  $commandCollector;
    /**
     * @var Model
     */

    public $model;
    function __construct(Walker $walker, Reader $reader)
    {
        $this->oidCollector = \SwitcherCore\Config\OidCollector::init($reader);
        $this->modelCollector = \SwitcherCore\Config\ModelCollector::init($reader);
        $this->commandCollector = \SwitcherCore\Config\CommandCollector::init($reader);
        $this->walker = $walker;
    }
    function connect($ip, $community) {
        $this->ip = $ip;
        $this->community = $community;
        $this->walker
            ->setIp($ip)
            ->setCommunity($community);
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
            $this->commandCollector->setModel($this->model);
            $this->oidCollector->readEnterpriceOids($this->model);
            $this->model->loadModules();
            //Implement objects for modules
            foreach ($this->model->getModules() as $moduleName=>$module) {
                $this->model->setModule(
                    $moduleName,
                    $module->setModel($this->model)
                        ->setOidCollector($this->oidCollector)
                        ->setWalker($this->walker)
                        ->setCommandCollector($this->commandCollector)
                );
            }

        } else {
            throw new \Exception("Returned empty response from walker, it's problem");
        }
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
}
