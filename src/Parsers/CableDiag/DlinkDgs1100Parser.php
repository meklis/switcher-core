<?php


namespace SwitcherCore\Parsers\CableDiag;

use SwitcherCore\Exceptions\IncompleteResponseException;
use \SwitcherCore\Parsers\AbstractParser;
use SwitcherCore\Parsers\Helper;
use SnmpWrapper\Request\PoollerRequest;


class DlinkDgs1100Parser extends DlinkParser
{
    public function walk($filter = [])
    {
        Helper::prepareFilter($filter) ;
        $ports_list = $this->getPortList($filter);
        $this->activateDiag($ports_list)->waitToDiag($ports_list);

        $ports_diag_result = [];
        foreach ($ports_list as $port=>$count_pairs) {
            $oids = [];
            if($count_pairs >= 2) {
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair1Status')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair2Status')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair1Length')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair2Length')->getOid(). ".{$port}";
            }
            if($count_pairs >= 4 ) {
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair3Status')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair4Status')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair3Length')->getOid(). ".{$port}";
                $oids[] = $this->oidsCollector->getOidByName('dlink.CableDiagPair4Length')->getOid(). ".{$port}";
            }
            $this->response = $this->formatResponse($this->walker->get($oids));

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
            usleep(5000);
        }
        if(count($ports_list) != 0) {
            throw new IncompleteResponseException("Not all ports are diagnosted");
        }
        return $this;
    }
    protected function activateDiag($ports_list) {
        foreach ($ports_list as $port=>$pairs) {
            $response = $this->formatResponse($this->walker->set(
                $this->oidsCollector->getOidByName('dlink.CableDiagAction')->getOid() . ".{$port}",
                PoollerRequest::TypeIntegerValue,
                1
            ));
            if(!isset($response['dlink.CableDiagAction'])) {
                throw new IncompleteResponseException("No response from device");
            }

        }
        return $this;
    }
}