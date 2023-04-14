<?php


namespace SwitcherCore\Modules\Dlink\SfpDiag;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;


class DefaultParser extends SwitchesPortAbstractModule
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
            $oids = [];
            $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair3Status')->getOid(). ".{$port}");
            $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair4Status')->getOid(). ".{$port}");
            $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair3Length')->getOid(). ".{$port}");
            $oids[] = Oid::init($this->oids->getOidByName('dlink.CableDiagPair4Length')->getOid(). ".{$port}");
            $this->response = $this->formatResponse($this->snmp->get($oids));



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
    protected function getPortList($filter) {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dlink.PortInfoMediumType')->getOid(), true),
        ]));
        $ports_list = [];
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($ports_list as $port=>$pairs) {
                if($interface['id'] != $port) {
                    unset($ports_list[$port]);
                }
            }
            return $ports_list;
        }
        foreach ($this->getResponseByName('dlink.PortInfoMediumType')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid());
            if($ident->getParsedValue() == 'Fiber') {
                 $ports_list[$port] = true;
            }
        }
        return $ports_list;
    }

}