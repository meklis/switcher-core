<?php


namespace SwitcherCore\Modules\Snmp\CableDiag;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class DlinkParser extends AbstractModule
{
    protected $ports = [];
    protected $formatedResponse = [];
    function parse($filter = [])
    {
        return $this->formatedResponse;
    }


    function getPretty()
    {
        return $this->parse();
    }
    function getPrettyFiltered($filter = [])
    {
        return $this->parse($filter);
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($filter) ;
        $ports_list = $this->getPortList($filter);
        $this->activateDiag($ports_list)->waitToDiag($ports_list);

        $ports_diag_result = [];
        foreach ($ports_list as $port=>$count_pairs) {
            $oids = [];
            if($count_pairs >= 2) {
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair1Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair2Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair1Length')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair2Length')->getOid(). ".{$port}");
            }
            if($count_pairs >= 4 ) {
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair3Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair4Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair3Length')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagPair4Length')->getOid(). ".{$port}");
            }
            $this->response = $this->formatResponse($this->obj->walker->get($oids));

            $pairs = [];
            if($count_pairs >= 2) {
                $pairs[] = [
                    'number' => 1,
                    'status' => $this->getResponseByName('dlink.CableDiagPair1Status')->fetchOne()->getParsedValue(),
                    'length' => $this->getResponseByName('dlink.CableDiagPair1Length')->fetchOne()->getParsedValue(),
                ];
                $pairs[] = [
                    'number' => 2,
                    'status' => $this->getResponseByName('dlink.CableDiagPair2Status')->fetchOne()->getParsedValue(),
                    'length' => $this->getResponseByName('dlink.CableDiagPair2Length')->fetchOne()->getParsedValue(),
                ];
            }
            if($count_pairs >= 4) {
                $pairs[] = [
                    'number' => 3,
                    'status' => $this->getResponseByName('dlink.CableDiagPair3Status')->fetchOne()->getParsedValue(),
                    'length' => $this->getResponseByName('dlink.CableDiagPair3Length')->fetchOne()->getParsedValue(),
                ];
                $pairs[] = [
                    'number' => 4,
                    'status' => $this->getResponseByName('dlink.CableDiagPair4Status')->fetchOne()->getParsedValue(),
                    'length' => $this->getResponseByName('dlink.CableDiagPair4Length')->fetchOne()->getParsedValue(),
                ];
            }

            $ports_diag_result[] = [
                'port' => $port,
                'pairs' => $pairs,
            ];
        }
        $this->formatedResponse = $ports_diag_result;

        return $this;
    }
    protected function waitToDiag($ports_list) {
        for ($i=0;$i<100;$i++) {
            foreach ($ports_list as $port=>$pairs) {
                $response = $this->formatResponse($this->obj->walker->get(
                    [Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagStatus')->getOid() . ".{$port}")]
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
        return $this;
    }
    protected function activateDiag($ports_list) {
        foreach ($ports_list as $port=>$pairs) {
            $response = $this->formatResponse($this->obj->walker->set(
                Oid::init($this->obj->oidCollector->getOidByName('dlink.CableDiagAction')->getOid() . ".{$port}",
                    false,
                PoollerRequest::TypeIntegerValue,
                1
            )));
            if(!isset($response['dlink.CableDiagAction'])) {
                throw new IncompleteResponseException("No response from device");
            } elseif ($response['dlink.CableDiagAction']->error()) {
                throw new IncompleteResponseException($response['dlink.CableDiagAction']->error());
            }
        }
        return $this;
    }
    protected function getPortList($filter) {
        $this->response = $this->formatResponse($this->obj->walker->walk([
            Oid::init($this->obj->oidCollector->getOidByName('if.Type')->getOid(), true),
            Oid::init($this->obj->oidCollector->getOidByName('if.OperStatus')->getOid()),
            Oid::init($this->obj->oidCollector->getOidByName('dlink.PortInfoMediumType')->getOid(), true),
        ]));
        $ports_list = [];
        foreach ($this->getResponseByName('if.Type')->fetchAll() as $ident) {
            if(!in_array($ident->getParsedValue(), ['GE', 'FE'])) {
                continue;
            }
            $port = Helper::getIndexByOid($ident->getOid());
            if(isset($this->obj->model->getExtra()['ge_ports']) && in_array($port, $this->obj->model->getExtra()['ge_ports'])) {
                $ident->setParsed('GE');
            }
            $pairs = 2;
            if($ident->getParsedValue() == 'GE') {
                $pairs = 4;
            }
            $ports_list[$port] = $pairs;
        }
        if($filter['port']) {
            foreach ($ports_list as $port=>$pairs) {
                if($filter['port'] != $port) {
                    unset($ports_list[$port]);
                }
            }
            return $ports_list;
        }
        foreach ($this->getResponseByName('dlink.PortInfoMediumType')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid(), 1);
            if($ident->getParsedValue() == 'Fiber') {
                unset($ports_list[$port]);
            }
        }

        if(!$this->obj->model->getExtra()['diag_linkup']) {
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