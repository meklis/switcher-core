<?php

namespace SwitcherCore\Modules\Edgecore;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class CableDiagES3510 extends AbstractInterfaces
{
    use InterfacesTrait;

    public function run($params = [])
    {
        $diagPorts = $this->getDiagPorts($params);
        $interfaces = $this->getInterfacesIds();
        $RESPONSES = [];
        $actionOid = $this->oids->getOidByName('cable_diag.action')->getOid();
        if($params['interface']) {
            foreach ($diagPorts as $snmpId => $countPairs) {
                //Start diag
                $response = $this->formatResponse($this->snmp->set(
                    Oid::init($actionOid,
                        false,
                        PoollerRequest::TypeIntegerValue,
                        $snmpId
                    )));
                if (!isset($response['cable_diag.action'])) {
                    throw new \SNMPException("No response from device");
                } elseif ($response['cable_diag.action']->error()) {
                    throw new \SNMPException($response['cable_diag.action']->error());
                }
            }
        }
        foreach ($diagPorts as $snmpId => $countPairs) {
            //Prepare oids
            RETRY_TEST:
            if(!$countPairs == 4) {
                $oids = [
                    Oid::init($this->oids->getOidByName('cable_diag.resultTime')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair1Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair2Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair3Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair4Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair1Length')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair2Length')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair3Length')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair4Length')->getOid().".{$snmpId}"),
                ];
            } else {
                $oids = [
                    Oid::init($this->oids->getOidByName('cable_diag.resultTime')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair1Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair2Status')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair1Length')->getOid().".{$snmpId}"),
                    Oid::init($this->oids->getOidByName('cable_diag.resultPair2Length')->getOid().".{$snmpId}"),
                ];
            }
            $response = $this->formatResponse($this->snmp->get($oids));

            $status = $this->getResponseByName('cable_diag.resultPair1Status', $response);
            if(!$status->error() ) {
                $result = $status->fetchAll();
                if(count($result) > 0 && $result[0]->getParsedValue() == 'UnderTesing') {
                    sleep(1);
                    goto RETRY_TEST;
                }
            }

            $pairs = [];
            $resultTime = null;
            foreach ($response as $oidName => $resp) {
                if($resp->error()) {
                    throw new \Exception($resp->error());
                }
                $match = [];
                if(preg_match('/^cable_diag\.resultTime/', $oidName)) {
                    $resultTime = $resp->fetchOne()->getParsedValue();
                    continue;
                } elseif (!preg_match('/^cable_diag\.resultPair([0-9])(.*)$/', $oidName, $match)) {
                    throw new \Exception("Error parse oid name");
                }
                $type = strtolower($match[2]);
                $pair = strtolower($match[1]);
                $pairs[$pair]['pair'] = (int)$pair;
                $pairs[$pair][$type] = $type == 'length' ? (int)$resp->fetchOne()->getValue() : $resp->fetchOne()->getParsedValue();
            }

            $RESPONSES[] = [
                'interface' => $interfaces[$snmpId],
                'pairs' => array_values($pairs),
                'result_time' => $resultTime,
            ];
        }
        $this->response = $RESPONSES;
        return $this;
    }

    protected function getCountPairsByPort($port) {
        $pairs = $this->model->getExtraParamByName('count_pairs');
        if(in_array($port, $this->model->getExtraParamByName('overwrite_pairs')['ports'])) {
            $pairs = $this->model->getExtraParamByName('overwrite_pairs')['count_pairs'];
        }
        return $pairs;
    }

    protected function getDiagPorts($params) {
        $forDiagPorts = [];
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $forDiagPorts[$interface['_snmp_id']] = $this->getCountPairsByPort($interface['_snmp_id']);
        } else {
            $interfaces = $this->getInterfacesIds();
            if (!$this->model->getExtraParamByName('diag_linkup')) {
                $oid = Oid::init($this->oids->getOidByName('if.OperStatus')->getOid());
                $response = $this->formatResponse($this->snmp->walk([$oid]));
                $operStatus = $this->getResponseByName('if.OperStatus', $response);
                if($operStatus->error()) {
                    throw new \Exception($operStatus->error());
                }
                foreach ($operStatus->fetchAll() as $status) {
                    if(!isset($interfaces[Helper::getIndexByOid($status->getOid())])) continue;
                    if($status->getParsedValue() == 'Up') continue;
                    $forDiagPorts[Helper::getIndexByOid($status->getOid())] = $this->getCountPairsByPort(Helper::getIndexByOid($status->getOid()));
                }
            }
        }
        return $forDiagPorts;
    }

    public function getPretty()
    {
            return  $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
