<?php

namespace SwitcherCore\Modules\Huawei;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class CableDiag extends AbstractInterfaces
{
    use InterfacesTrait;

    public function run($params = [])
    {
        $diagPorts = $this->getDiagPorts($params);
        $interfaces = $this->getInterfacesIds();
        $RESPONSES = [];
        $actionOid = $this->oids->getOidByName('cable_diag.action')->getOid();
        $statusOid = $this->oids->getOidByName('cable_diag.status')->getOid();
        $lengthOid = $this->oids->getOidByName('cable_diag.length')->getOid();

        if($this->model->getExtraParamByName('only_manual_diag') && !$params['interface']) {
            $this->response = [];
            return  $this;
        }


        foreach ($diagPorts as $snmpId) {
                //Start diag
                $response = $this->formatResponse($this->snmp->set(
                    Oid::init($actionOid . ".{$snmpId}",
                        false,
                        PoollerRequest::TypeIntegerValue,
                        1
                    )));
                if (!isset($response['cable_diag.action'])) {
                    throw new \SNMPException("No response from device");
                } elseif ($response['cable_diag.action']->error()) {
                    throw new \SNMPException($response['cable_diag.action']->error());
                }

                $response = $this->formatResponse($this->snmp->get([
                    Oid::init("{$statusOid}.{$snmpId}"),
                    Oid::init("{$lengthOid}.{$snmpId}"),
                ]));

                if (!isset($response['cable_diag.length'])) {
                    throw new \SNMPException("No response from device");
                } elseif ($response['cable_diag.length']->error()) {
                    throw new \SNMPException($response['cable_diag.length']->error());
                }

                if (!isset($response['cable_diag.status'])) {
                    throw new \SNMPException("No response from device");
                } elseif ($response['cable_diag.status']->error()) {
                    throw new \SNMPException($response['cable_diag.status']->error());
                }

                $RESPONSES[] = [
                  'interface' => $interfaces[$snmpId],
                  'status' => $response['cable_diag.status']->fetchOne()->getParsedValue(),
                  'length' => $response['cable_diag.length']->fetchOne()->getParsedValue(),
                ];
        }
        $this->response = $RESPONSES;
        return $this;
    }

    protected function getDiagPorts($params) {
        $forDiagPorts = [];
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            $forDiagPorts[] = $interface['_snmp_id'];
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
                    $forDiagPorts[] = Helper::getIndexByOid($status->getOid());
                }
            }
        }
        return $forDiagPorts;
    }

    public function getPretty()
    {
        if(count($this->response) == 1) {
            return $this->response[0];
        } else if(count($this->response) == 0) {
            return  null;
        } else {
            return  $this->response;
        }
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
