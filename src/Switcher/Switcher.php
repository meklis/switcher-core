<?php

namespace SnmpSwitcher\Switcher;

use mysql_xdevapi\Exception;
use SnmpSwitcher\Exceptions\ParserNotFoundException;
use \SnmpWrapper\Walker;
use \SnmpSwitcher\Config\ModelCollector;
use \SnmpSwitcher\Config\Objects\Model;
use \SnmpSwitcher\Config\OidCollector;


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
    function getLinkInfo($port = 0, $ethernetOnly=true) {
        $type = '';
        if($ethernetOnly) {
            $type = 'gigabitEthernet,ethernet';
        }
        return $this->getParser('link')->walk([
            'port' => $port,
        ])->getPrettyFiltered(['type' => $type]);
    }
    function getCounters($port = 0) {
        return $this->getParser('counters')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getErrors($port = 0) {
         return $this->getParser('errors')->walk([
                'port' => $port,
            ])->getPretty();
    }
    function getRmon($port) {
        return $this->getParser('rmon')->walk([
            'port' => $port,
        ])->getPretty();
    }
    function getFDB($port = 0, $vlan = 0, $mac = "")
    {
        return $this->getParser('fdb')->walk([
            'mac' => $mac,
            'vlan_id' => $vlan,
        ])->getPrettyFiltered([
            'port' => $port,
        ]);
    }
    function getVlans($vlanId = 0) {
        return $this->getParser('vlan')->walk(['vlan_id'=>$vlanId])->getPrettyFiltered();
    }
    function getVlansByPort($port = 0) {
        $parser =  $this->getParser('vlan');
        $data = $parser->walk()->getPrettyFiltered();
        $indexes = $parser->getIndexes();
        $response = [];
        foreach ($indexes as $index=>$port) {
            $untagged_vlans = [];
            $tagged_vlans = [];
            $egress_vlans = [];
            $forbidden_vlans = [];
            foreach ($data as $d) {
                if(in_array($port, $d['ports']['untagged'])) $untagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port, $d['ports']['egress'])) $egress_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port, $d['ports']['tagged'])) $tagged_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
                if(in_array($port, $d['ports']['forbidden'])) $forbidden_vlans[] = [
                    'name' => $d['name'],
                    'id' => $d['id'],
                ];
            }
            $response[] = [
                'port' => $port,
                'untagged' => $untagged_vlans,
                'tagged' => $tagged_vlans,
                'egress' => $egress_vlans,
                'forbidden' => $forbidden_vlans,
            ];
        }
        return $response;
    }
    function getPVID($port = 0) {
        return $this->getParser('pvid')->walk(['port'=>$port])->getPretty();
    }
    function getCableDiag($port = 0, $disable_diag_on_link_up = true) {
        return $this->getParser('cable_diag')->walk(['port'=>$port, 'disa_linkup_diag' => $disable_diag_on_link_up])->getPretty();
    }
}
