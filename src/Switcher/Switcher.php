<?php

namespace SwitcherCore\Switcher;

use Meklis\TelnetOverProxy\Telnet;
use SnmpWrapper\Walker;
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
     * @var Telnet
     */
    public $telnet;
    /**
     * @var OidCollector
     */
    public  $oidCollector;
    /**
     * @var Model
     */

    public $model;
    function __construct(Reader $reader)
    {
        $this->oidCollector = \SwitcherCore\Config\OidCollector::init($reader);
        $this->modelCollector = \SwitcherCore\Config\ModelCollector::init($reader);

    }
    function setWalker(Walker $walker) {
        $this->walker = $walker;
    }
    function setTelnet(Telnet $telnet) {
        $this->telnet = $telnet;
    }

    function detectModel($ip, $community) {
        $this->ip = $ip;
        $this->community = $community;
        if(!$this->walker) {
            throw new \Exception("Snmp walker not setted. You must set walker before connect");
        }
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
            $this->oidCollector->readEnterpriceOids($this->model);
            $this->model->loadModules();
            //Implement objects for modules
            foreach ($this->model->getModules() as $moduleName=>$module) {
                $this->model->setModule(
                    $moduleName,
                    $module->setModel($this->model)
                        ->setOidCollector($this->oidCollector)
                        ->setWalker($this->walker)
                );
            }

        } else {
            throw new \Exception("Returned empty response from walker, it's problem");
        }
    }
    function loginTelnet($login, $password) {
        $conn_type = $this->model->getTelnetConnType();
        switch ($conn_type){
            case 'dlink':
                $this->conn->disableMagicControl()
                    ->setLinuxEOL()
                    ->login($login, $password, 'dlink')
                    ->exec("disa clip");
                break;
            default:
                throw new \Exception("Not supported connection type '$conn_type' ");
        }

        foreach ($this->model->getModules() as $module) {
            $module->setTelnetConn($this->telnet);
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
}
