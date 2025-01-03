<?php


namespace SwitcherCore\Modules\Dlink;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class SwitchesPortAbstractModule extends AbstractModule
{
    protected $indexesPort =[];
    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        if($cached = $this->getCache('port_indexes', true)) {
            return $cached;
        }
        $response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Name')->getOid()),
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
        ]));
        $types = [];
        foreach ($response['if.Type']->fetchAll() as $resp) {
            $types[Helper::getIndexByOid($resp->getOid())] = $resp->getParsedValue();
        }

        foreach ($response['if.Name']->fetchAll() as $resp) {
            if(isset($types[Helper::getIndexByOid($resp->getOid())]) &&
                $ethernetOnly &&
                in_array($types[Helper::getIndexByOid($resp->getOid())], ['FE','GE']) &&
                strpos($resp->getValue(), "ch") !== 0
            ) {
                $indexes[Helper::getIndexByOid($resp->getOid())] = [
                    'id' =>  (int) Helper::getIndexByOid($resp->getOid()),
                    'name' => $resp->getValue(),
                    '_key' => Helper::getIndexByOid($resp->getOid()),
                    'type' => $types[Helper::getIndexByOid($resp->getOid())],
                ];
            }
        }
        $this->indexesPort = $indexes;
        $this->setCache('port_indexes', $indexes, 600, true);
        return $indexes;
    }

    function parseInterface($interface) {
        $interfaces = $this->getIndexes();
        if(preg_match('/^[0-9]{1,}$/', $interface)) {
            if(isset($interfaces[$interface])) {
                return $interfaces[$interface];
            } else {
                throw new \Exception("Interface with id '$interface' not found in device");
            }
        } else {
            foreach ($interfaces as $iface) {
                if($iface['name'] === $interface) {
                    return $iface;
                }
            }
            throw new \Exception("Interface with name|key '$interface' not found in device");
        }
    }
}