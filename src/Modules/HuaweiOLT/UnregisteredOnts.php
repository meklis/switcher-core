<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UnregisteredOnts extends HuaweiOLTAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        return $this->response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('ont.autofind..*'));

        $response = $this->formatResponse($this->snmp->walkNext($oids));

        $data = [];
        if (isset($response['ont.autofind.sn'])) {
            if ($response['ont.autofind.sn']->error()) {
                return null;
            }
            foreach ($response['ont.autofind.sn']->fetchAll() as $sn) {
                $iface = $this->findIfaceByOid($sn->getOid());
                $blocks = explode(":", $sn->getHexValue());
                $data[$iface['id']] = [
                    '_serial_asci' => $this->convertHexToString("{$blocks[0]}:{$blocks[1]}:{$blocks[2]}:{$blocks[3]}") .
                        $blocks[4] . $blocks[5] . $blocks[6] . $blocks[7]
                    ,
                    'serial' => str_replace(":", "", $sn->getHexValue()),
                    'interface' => $iface,
                    'password' => null,
                    'version' => null,
                    'equipment_id' => null,
                    'fw_version' => null,
                    'check_code' => null,
                    'loid' => null,
                    'reg_time' => null,
                    'model' => null,
                    'type' => null,
                ];
            }
        }
        if (isset($response['ont.autofind.password'])) {
            foreach ($response['ont.autofind.password']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['password'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.autofind.version'])) {
            foreach ($response['ont.autofind.version']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['version'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.autofind.softwareVer'])) {
            foreach ($response['ont.autofind.softwareVer']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['fw_version'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.autofind.equipmentId'])) {
            foreach ($response['ont.autofind.equipmentId']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['equipment_id'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.autofind.checkCode'])) {
            foreach ($response['ont.autofind.checkCode']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['check_code'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.autofind.loid'])) {
            foreach ($response['ont.autofind.loid']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['loid'] = $this->convertHexToString($d->getHexValue());
            }
        }
        $this->response = array_values($data);
        return $this;
    }
}

