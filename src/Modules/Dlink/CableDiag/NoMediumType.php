<?php


namespace SwitcherCore\Modules\Dlink\CableDiag;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;


class NoMediumType extends SwitchesPortAbstractModule
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

        $ports_diag_result = [];
        foreach ($ports_list as $port=>$count_pairs) {
            $this->activateDiag($port)->waitToDiag($port);
            $oids = [];
            if($count_pairs >= 2) {
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair1Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair2Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair1Length')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair2Length')->getOid(). ".{$port}");
            }
            if($count_pairs >= 4 ) {
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair3Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair4Status')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair3Length')->getOid(). ".{$port}");
                $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair4Length')->getOid(). ".{$port}");
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));

            $pairs = [];
            if($count_pairs >= 2) {
                $pairs[] = [
                    'number' => 1,
                    'status' => $this->getResponseByName('dlink.CableDiagPair1Status')->fetchOne()->getParsedValue(),
                    'length' =>(int) $this->getResponseByName('dlink.CableDiagPair1Length')->fetchOne()->getParsedValue(),
                ];
                $pairs[] = [
                    'number' => 2,
                    'status' => $this->getResponseByName('dlink.CableDiagPair2Status')->fetchOne()->getParsedValue(),
                    'length' =>(int) $this->getResponseByName('dlink.CableDiagPair2Length')->fetchOne()->getParsedValue(),
                ];
            }
            if($count_pairs >= 4) {
                $pairs[] = [
                    'number' => 3,
                    'status' => $this->getResponseByName('dlink.CableDiagPair3Status')->fetchOne()->getParsedValue(),
                    'length' =>(int) $this->getResponseByName('dlink.CableDiagPair3Length')->fetchOne()->getParsedValue(),
                ];
                $pairs[] = [
                    'number' => 4,
                    'status' => $this->getResponseByName('dlink.CableDiagPair4Status')->fetchOne()->getParsedValue(),
                    'length' =>(int) $this->getResponseByName('dlink.CableDiagPair4Length')->fetchOne()->getParsedValue(),
                ];
            }

            $ports_diag_result[] = [
                'interface' => $this->parseInterface($port),
                'pairs' => $pairs,
            ];
        }
        $this->formatedResponse = $ports_diag_result;

        return $this;
    }
    protected function waitToDiag($port) {
        for ($i=0;$i<500;$i++) {
            usleep(5000);
                $response = $this->formatResponse($this->snmp->get(
                    [Oid::init($this->oids->getOidByName('dlink.CableDiagAction')->getOid() . ".{$port}")]
                ));
                if(isset($response['dlink.CableDiagAction']) && $response['dlink.CableDiagAction']->fetchOne()->getParsedValue() !== 'Processing') {
                    return $this;
                }
            usleep(8000);
        }
        return $this;
    }
    protected function activateDiag($port) {
        $response = $this->formatResponse($this->snmp->set(
            Oid::init($this->oids->getOidByName('dlink.CableDiagAction')->getOid() . ".{$port}",
                false,
            PoollerRequest::TypeIntegerValue,
            1
        )));
        if(!isset($response['dlink.CableDiagAction'])) {
            throw new IncompleteResponseException("No response from device");
        } elseif ($response['dlink.CableDiagAction']->error()) {
            throw new IncompleteResponseException($response['dlink.CableDiagAction']->error());
        }
        return $this;
    }
    protected function getPortList($filter) {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dlink.CableDiagPortType')->getOid(), true),
            Oid::init($this->oids->getOidByName('if.OperStatus')->getOid()),
        ]));
        $ports_list = [];
        foreach ($this->getResponseByName('dlink.CableDiagPortType')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid());
            if(isset($this->model->getExtra()['ge_ports']) && in_array($port, $this->model->getExtra()['ge_ports'])) {
                $ident->setParsed('GE');
            }
            $pairs = 2;
            if($ident->getParsedValue() == 'GE') {
                $pairs = 4;
            }
            $ports_list[$port] = $pairs;
        }
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($ports_list as $port=>$pairs) {
                if($interface['id'] != $port) {
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