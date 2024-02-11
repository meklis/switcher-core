<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntSerial extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [];
        $serial = $this->getResponseByName('ont.serial', $this->response);
        if ($serial->error()) {
            throw new \Exception($serial->error());
        }
        foreach ($serial->fetchAll() as $o) {
            try {
                $iface = $this->parseInterface(Helper::getIndexByOid($o->getOid(), 1) . "." . Helper::getIndexByOid($o->getOid()));

                if ($iface['type'] !== 'ONU') continue;
                $data[] = [
                    'interface' => $iface,
                    'serial' => strtoupper(str_replace(":", "", $o->getParsedValue())),
                ];
            } catch (\Exception $e) {
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
        //if.PhysAddr
        $oid = $this->oids->getOidByName('ont.serial')->getOid();
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $this->response = $this->formatResponse(
                $this->snmp->get([Oid::init($oid . $iface['_snmp_id'])])
            );
        } else {
            $this->response = $this->formatResponse(
                $this->snmp->walk([Oid::init($oid)])
            );
        }
        return $this;
    }
}

