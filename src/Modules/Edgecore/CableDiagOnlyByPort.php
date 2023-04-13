<?php

namespace SwitcherCore\Modules\Edgecore;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class CableDiagOnlyByPort extends AbstractInterfaces
{
    use InterfacesTrait;

    public function run($params = [])
    {
        if (!$params['interface']) {
            $this->response = [];
            $this->logger->error("Cable diagnostic supported only on concreted port");
            return $this;
        }
        $iface = $this->parseInterface($params['interface']);
        $actionOid = $this->oids->getOidByName('cable_diag.action')->getOid();
        $response = $this->formatResponse($this->snmp->set(
            Oid::init($actionOid,
                false,
                PoollerRequest::TypeIntegerValue,
                $iface['_snmp_id']
            )));
        if (!isset($response['cable_diag.action'])) {
            throw new \SNMPException("No response from device");
        } elseif ($response['cable_diag.action']->error()) {
            throw new \SNMPException($response['cable_diag.action']->error());
        }
        $countPairs = $this->getCountPairsByPort($iface['_snmp_id']);
        //Prepare oids
        $repeats = 0;
        RETRY_TEST:
        $repeats++;
        if ($countPairs == 4) {
            $oids = [
                Oid::init($this->oids->getOidByName('cable_diag.resultTime')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair1Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair2Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair3Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair4Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair1Length')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair2Length')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair3Length')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair4Length')->getOid() . ".{$iface['_snmp_id']}"),
            ];
        } else {
            $oids = [
                Oid::init($this->oids->getOidByName('cable_diag.resultTime')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair1Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair2Status')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair1Length')->getOid() . ".{$iface['_snmp_id']}"),
                Oid::init($this->oids->getOidByName('cable_diag.resultPair2Length')->getOid() . ".{$iface['_snmp_id']}"),
            ];
        }
        sleep(1);
        $response = $this->formatResponse($this->snmp->get($oids));

        $status = $this->getResponseByName('cable_diag.resultPair1Status', $response);
        if (!$status->error()) {
            $result = $status->fetchAll();
            if ($repeats < 10 && count($result) > 0 && ($result[0]->getParsedValue() == 'UnderTesing' || $result[0]->getParsedValue() == 'NotTested')) {
                goto RETRY_TEST;
            }
        }

        $pairs = [];
        $resultTime = null;
        foreach ($response as $oidName => $resp) {
            if ($resp->error()) {
                throw new \Exception($resp->error());
            }
            $match = [];
            if (preg_match('/^cable_diag\.resultTime/', $oidName)) {
                $resultTime = $resp->fetchOne()->getParsedValue();
                continue;
            } elseif (!preg_match('/^cable_diag\.resultPair([0-9])(.*)$/', $oidName, $match)) {
                throw new \Exception("Error parse oid name");
            }
            $type = strtolower($match[2]);
            $pair = strtolower($match[1]);
            $pairs[$pair]['number'] = (int)$pair;
            $pairs[$pair][$type] = $type == 'length' ? (int)$resp->fetchOne()->getValue() : $resp->fetchOne()->getParsedValue();
        }

        $RESPONSES[] = [
            'interface' => $iface,
            'pairs' => array_values($pairs),
            'result_time' => $resultTime,
        ];
        $this->response = $RESPONSES;
        return $this;
    }

    protected function getCountPairsByPort($port)
    {
        $pairs = $this->model->getExtraParamByName('count_pairs');
        if (in_array($port, $this->model->getExtraParamByName('overwrite_pairs')['ports'])) {
            $pairs = $this->model->getExtraParamByName('overwrite_pairs')['count_pairs'];
        }
        return $pairs;
    }

    protected function getDiagPorts($params)
    {
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
                if ($operStatus->error()) {
                    throw new \Exception($operStatus->error());
                }
                foreach ($operStatus->fetchAll() as $status) {
                    if (!isset($interfaces[Helper::getIndexByOid($status->getOid())])) continue;
                    if ($status->getParsedValue() == 'Up') continue;
                    $forDiagPorts[Helper::getIndexByOid($status->getOid())] = $this->getCountPairsByPort(Helper::getIndexByOid($status->getOid()));
                }
            }
        }
        return $forDiagPorts;
    }

    public function getPretty()
    {

        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
