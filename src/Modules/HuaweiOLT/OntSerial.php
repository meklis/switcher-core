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
        foreach ($this->getResponseByName('ont.gpon.config.ident')->fetchAll() as $d) {
            $blocks = explode(":", $d->getHexValue());
            $serialASCII =  $this->convertHexToString("{$blocks[0]}:{$blocks[1]}:{$blocks[2]}:{$blocks[3]}") .
                $blocks[4] . $blocks[5] . $blocks[6] . $blocks[7];
            $serialHEX =  str_replace(":", "",$d->getHexValue());
            $resp[] = [
                'interface' => $this->findIfaceByOid($d->getOid()),
                '_serial_ascii' => $serialASCII,
                'serial' =>  $filter['sn_as_ascii'] ? $serialASCII : $serialHEX,
                '_serial_hex' =>  $serialHEX,
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
        $oid = $this->oids->getOidByName('ont.gpon.config.ident');
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

