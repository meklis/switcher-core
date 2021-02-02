<?php


namespace SwitcherCore\Modules\Dlink;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class SwitchesPortAbstractModule extends AbstractModule
{

    protected $indexesPort;

    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        $response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Index')->getOid()),
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
        ]));
        $types = [];
        foreach ($response['if.Type']->fetchAll() as $resp) {
            $types[Helper::getIndexByOid($resp->getOid())] = $resp->getParsedValue();
        }

        foreach ($response['if.Index']->fetchAll() as $resp) {
            if($ethernetOnly && in_array($types[Helper::getIndexByOid($resp->getOid())], ['FE','GE'])) {
                $indexes[Helper::getIndexByOid($resp->getOid())] = $resp->getValue();
            }
        }
        $this->indexesPort = $indexes;
        return $indexes;
    }
}