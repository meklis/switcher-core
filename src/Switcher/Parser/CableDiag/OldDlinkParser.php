<?php


namespace SnmpSwitcher\Switcher\Parser\CableDiag;

use SnmpSwitcher\Exceptions\IncompleteResponseException;
use \SnmpSwitcher\Switcher\Parser\AbstractParser;
use SnmpSwitcher\Switcher\Parser\Helper;
use SnmpWrapper\Request\PoollerRequest;


class OldDlinkParser extends AbstractParser
{
    protected $ports = [];
    function parse($filter = [])
    {
        parent::parse();
    }

    function getRaw()
    {
        // TODO: Implement getRaw() method.
    }

    function getPretty()
    {
        // TODO: Implement getSwitchData() method.
    }
    function getPrettyFiltered($filter = [])
    {
        // TODO: Implement getPrettyFiltered() method.
    }

    public function walk($filter = [])
    {
        Helper::prepareFilter($filter) ;
        $ports_list = $this->getPortList($filter);
        $this->activateDiag($ports_list);


        //Request\PoollerRequest::TypeOctetStringValue|

        return $this;
    }
    protected function waitToDiag($ports_list) {
        for ($i=0;$i<50;$i++) {
            foreach ($ports_list as $port=>$pairs) {
                $response = $this->formatResponse($this->walker->get(
                    [$this->oidsCollector->getOidByName('dlink.CableDiagStatus')->getOid() . ".{$port}"]
                ));
                if(isset($response['dlink.CableDiagStatus']) && $response['dlink.CableDiagStatus']->fetchOne()->getParsedValue() != 'Proccessing') {
                    unset($ports_list[$port]);
                }
            }
            if(count($ports_list) == 0) {
                break;
            }
            usleep(50000);
        }
        if(count($ports_list) != 0) {
            throw new IncompleteResponseException("Not all ports are diagnosted");
        }
    }
    protected function activateDiag($ports_list) {
        foreach ($ports_list as $port=>$pairs) {
            for($i=0;$i<3;$i++) {
                $response = $this->formatResponse($this->walker->set(
                    $this->oidsCollector->getOidByName('dlink.CableDiagAction')->getOid() . ".{$port}",
                    PoollerRequest::TypeIntegerValue,
                    1
                ));
                if(!isset($response['dlink.CableDiagAction'])) {
                    throw new IncompleteResponseException("No response from device");
                }
                $resp  = $response['dlink.CableDiagAction'];
                if(!$resp->error() && $resp->fetchOne()->getValue() == 3) {
                    break;
                }
            }
        }
    }
    protected function getPortList($filter) {
        $this->response = $this->formatResponse($this->walker->walk([
            $this->oidsCollector->getOidByName('if.Type')->getOid(),
            $this->oidsCollector->getOidByName('if.OperStatus')->getOid(),
        ]));
        $ports_list = [];
        foreach ($this->getResponseByName('if.Type')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid());
            $pairs = 2;
            if($ident->getParsedValue() == 'GE') {
                $pairs = 4;
            }
            $ports_list[$port] = $pairs;
        }
        foreach ($ports_list as $port=>$pairs) {
            if(!in_array($port, $this->model->getExtra()['diag_ports'])) {
                unset($ports_list[$port]);
            }
        }
        if($filter['port']) {
            if(!in_array($filter['port'], $this->model->getExtra()['diag_ports'])) {
                throw new \InvalidArgumentException("Incorrect port. Port not exist or denied for diag.");
            }
            foreach ($ports_list as $port=>$pairs) {
                if($filter['port'] != $port) {
                    unset($ports_list[$port]);
                }
            }
            return $ports_list;
        }
        if(!$this->model->getExtra()['diag_linkup']) {
            foreach ($this->getResponseByName('if.OperStatus')->fetchAll() as $ident) {
                $port = Helper::getIndexByOid($ident->getOid());
                if($ident->getParsedValue() == 'Up') {
                    unset($ports_list[$port]);
                }
            }
        }
        return $ports_list;
    }
}