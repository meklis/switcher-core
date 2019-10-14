<?php

namespace SwitcherCore\Switcher;

use SwitcherCore\Exceptions\ParserNotFoundException;
use \SnmpWrapper\Walker;
use \SwitcherCore\Config\ModelCollector;
use \SwitcherCore\Config\Objects\Model;
use \SwitcherCore\Config\OidCollector;


class Switcher
{
    /**
     * @var ModelCollector
     */
    protected $modelCollector;
    /**
     * @var Walker
     */
    protected $walker;
    /**
     * @var OidCollector
     */
    protected $oidCollector;
    /**
     * @var Model
     */

    protected $model;
    function __construct(Walker $walker, ModelCollector $modelCollector, OidCollector $oidCollector)
    {
        $this->modelCollector = $modelCollector;
        $this->walker = $walker;
        $this->oidCollector = $oidCollector;
    }
    function connect($ip, $community) {
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
            $this->model->loadParsers();
            //Implement objects for parsers
            foreach ($this->model->getParsers() as $parserName=>$parser) {
                $this->model->setParser(
                    $parserName,
                    $parser->setModel($this->model)->setOidCollector($this->oidCollector)->setWalker($this->walker)
                );
            }

        } else {
            throw new \Exception("Returned empty response from walker, it's problem");
        }
    }
    protected function getParser($parserName) {
        if(!$this->model) {
            throw new \Exception("Device properties and oids not loaded. Are you use ::connect() first?");
        }
        if(isset($this->model->getParsers()[$parserName])) {
            return $this->model->getParsers()[$parserName];
        } else {
            throw new ParserNotFoundException("Parser $parserName not found for model {$this->model->getName()}");
        }
    }
    function getSystemInfo() {
        return $this->getParser('system')->walk()->getPretty();
    }
}
