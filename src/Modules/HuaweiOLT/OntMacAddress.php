<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends HuaweiOLTAbstractModule
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
        if(!$this->isHasEponIfaces()) return [];
        $resp = [];
        foreach ($this->getResponseByName('ont.epon.config.ident')->fetchAll() as $d) {
            $resp[] = [
                'interface' => $this->findIfaceByOid($d->getOid()),
                'mac_address' => $d->getHexValue(),
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
        if(!$this->isHasEponIfaces()) return $this;
        $oid = $this->oids->getOidByName('ont.epon.config.ident');
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

