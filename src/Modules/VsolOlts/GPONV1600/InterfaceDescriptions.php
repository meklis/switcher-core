<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceDescriptions extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPretty()
    {
        return $this->getPrettyFiltered();
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if($dt->error()) {
                throw new \SNMPException($dt->error());
            }
            foreach ($dt->fetchAll() as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if ($oidName == 'ont.description') {
                    $id = ".". Helper::getIndexByOid($resp->getOid(), 1) . "." .Helper::getIndexByOid($resp->getOid());
                }
                $interface = $this->parseInterface($id);
                $data[] = [
                    'interface' => $interface,
                    'description' => $this->convertHexToString($resp->getHexValue()),
                ];
            }
        }
        return $data;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] == 'ONU') {
                $oids = [\SnmpWrapper\Oid::init($this->oids->getOidByName('ont.description')->getOid() . "{$interface['_snmp_id']}"),];
            } else {
                $oids = [\SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid() . "{$interface['_snmp_id']}"),];
            }
            $this->response = $this->formatResponse(
                $this->snmp->get($oids)
            );
            return $this;
        }

        $getOidsForPhysical = function () {
           $oid = $this->oids->getOidByName('if.Alias')->getOid();
           $oids = [];
           foreach ($this->getPhysicalInterfaces() as $iface) {
               $oids[] = Oid::init($oid . $iface['_snmp_id']);
           };
           return $oids;
        };

        if ($filter['interface_type'] == 'ONU') {
            $data = $this->formatResponse(
                $this->snmp->walk([\SnmpWrapper\Oid::init($this->oids->getOidByName('ont.description')->getOid()),])
            );
        } elseif ($filter['interface_type'] == 'PHYSICAL') {
            $data = $this->formatResponse(
                $this->snmp->get($getOidsForPhysical())
            );
        } else {
            $data = array_merge(
                $this->formatResponse(
                    $this->snmp->walk([\SnmpWrapper\Oid::init($this->oids->getOidByName('ont.description')->getOid()),])
                ),
                $this->formatResponse(
                    $this->snmp->get($getOidsForPhysical())
                )
            );
        }
        $this->response = $data;
        return $this;
    }
}

