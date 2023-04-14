<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

trait WalkerOverGet
{

    /**
     * @var MultiWalkerInterface
     */
    protected $snmp;

    /**
     * @param $ifacesList
     * @param \SwitcherCore\Config\Objects\Oid[] $oids
     * @return WrappedResponse[]
     */
    function snmpGetByInterfaces(array $ifacesList, array $oids)
    {
        $oidObjects = [];
        foreach ($oids as $oid) {
            foreach ($ifacesList as $iface) {
                    $oidObjects[] = Oid::init("{$oid->getOid()}.{$iface['_snmp_id']}");
            }
        }
        $resp = $this->formatResponse($this->snmp->get($oidObjects));
        return $resp;
    }
    /**
     * @param PoollerResponse[] $response
     * @return WrappedResponse[]
     *
     * @throws \Exception
     */
    abstract function formatResponse(array $response);
}