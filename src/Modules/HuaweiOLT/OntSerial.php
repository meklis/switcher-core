<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntSerial extends HuaweiOLTAbstractModule
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
        $resp = [];
        foreach ($this->getResponseByName('ont.config.sn')->fetchAll() as $d) {
            $resp[] = [
                'interface' => $this->findIfaceByOid($d->getOid()),
                'serial' =>  str_replace(":", "",$d->getHexValue()),
                '_vendor_prefix' => $this->convertHexToString(substr($d->getHexValue(), 0, 11)),
            ];
        }
        return $resp;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oid = $this->oids->getOidByName('ont.config.sn');
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oid = $oid->getOid() . "." . $iface['xid'];
            $this->response = $this->formatResponse($this->snmp->get([Oid::init($oid)]));
        } else {
            $oid = $oid->getOid();
            $this->response = $this->formatResponse($this->snmp->walk([Oid::init($oid)]));
        }
        return $this;
    }
}

