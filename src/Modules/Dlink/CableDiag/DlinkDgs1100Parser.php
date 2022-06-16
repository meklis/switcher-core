<?php


namespace SwitcherCore\Modules\Dlink\CableDiag;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SnmpWrapper\Oid as O;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;


class DlinkDgs1100Parser extends SwitchesPortAbstractModule
{
    public function run($filter = [])
    {
        $ports_list = [];
        if($filter['interface']) {
            $ports_list[] = $this->parseInterface($filter['interface']);
        } else {
            $ports_list = $this->getIndexes();
            if($nonDiag = $this->model->getExtraParamByName('non_diag_ports')) {
                $ports_list = array_values(array_filter($ports_list, function ($e) use ($nonDiag){
                   return !in_array($e['id'], $nonDiag);
                }));
            }
            if(!$this->model->getExtraParamByName('diag_linkup')) {
                $upIds = $this->getUpInterfaceIds();
                $ports_list = array_filter($ports_list, function ($e) use ($upIds) {
                   return !in_array($e['id'], $upIds);
                });
            }
        }

        $ports_diag_result = [];
        $oidsDiag = [
            O::init($this->oids->getOidByName('dlink.cableDiagPair1TestResult')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair1FaultDistance')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair2TestResult')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair2FaultDistance')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair3TestResult')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair3FaultDistance')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair4TestResult')->getOid()),
            O::init($this->oids->getOidByName('dlink.cableDiagPair4FaultDistance')->getOid()),
        ];
        foreach ($ports_list as $interface) {

            $triggerResult  = $this->formatResponse($this->snmp->set(
                O::init($this->oids->getOidByName('dlink.cableDiagTriggerIndex')->getOid(),
                false,
                PoollerRequest::TypeIntegerValue,
                $interface['id']
            )))['dlink.cableDiagTriggerIndex']->fetchOne();

            $this->response = $this->formatResponse($this->snmp->get($oidsDiag));
            $ports_diag_result[] = [
                'interface' => $interface,
                'pairs' => [
                    [
                       'number' => 1,
                       'status' => $this->getResponseByName('dlink.cableDiagPair1TestResult')->fetchOne()->getParsedValue(),
                       'length' => (int)$this->getResponseByName('dlink.cableDiagPair1FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 2,
                       'status' => $this->getResponseByName('dlink.cableDiagPair2TestResult')->fetchOne()->getParsedValue(),
                       'length' => (int)$this->getResponseByName('dlink.cableDiagPair2FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 3,
                       'status' => $this->getResponseByName('dlink.cableDiagPair3TestResult')->fetchOne()->getParsedValue(),
                       'length' => (int)$this->getResponseByName('dlink.cableDiagPair3FaultDistance')->fetchOne()->getValue(),
                    ],
                    [
                       'number' => 4,
                       'status' => $this->getResponseByName('dlink.cableDiagPair4TestResult')->fetchOne()->getParsedValue(),
                       'length' => (int)$this->getResponseByName('dlink.cableDiagPair4FaultDistance')->fetchOne()->getValue(),
                    ],
                ]
            ];
        }
        $this->formatedResponse = $ports_diag_result;

        return $this;
    }

    protected function getUpInterfaceIds() {
        $responses = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.OperStatus')->getOid())
        ]));
        $response = $this->getResponseByName('if.OperStatus', $responses);
        if($response->error()) {
            throw new \SNMPException($response->error());
        }
        $RETURN = [];
        foreach ($response->fetchAll() as $resp) {
            if($resp->getParsedValue() == 'Up') {
                $RETURN[] = Helper::getIndexByOid($resp->getOid());
            }
        }
        return $RETURN;
    }

    /**
     * @return array
     */
    public function getPretty()
    {
       return $this->formatedResponse;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->formatedResponse;
    }
}