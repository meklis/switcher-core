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
        $oidRequests = [];
        if($this->isHasGponIfaces()) $oidRequests = $this->oids->getOidsByRegex('ont.gpon.autofind..*');
        if($this->isHasEponIfaces()) $oidRequests = $this->oids->getOidsByRegex('ont.epon.autofind..*');
        $oids = array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $oidRequests);
        $response = $this->formatResponse($this->snmp->walkNext($oids));
        $this->response = array_values(array_merge($this->getGponUnregisteredFromResponses($response), $this->getEponUnregisteredFromResponses($response)));
        return $this;
    }

    function getGponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.gpon.autofind.ident'])) {
            if ($response['ont.gpon.autofind.ident']->error()) {
                return [];
            }
            foreach ($response['ont.gpon.autofind.ident']->fetchAll() as $sn) {
                $iface = $this->findIfaceByOid($sn->getOid());
                $blocks = explode(":", $sn->getHexValue());
                $data[$iface['id']] = [
                    '_serial_ascii' => $this->convertHexToString("{$blocks[0]}:{$blocks[1]}:{$blocks[2]}:{$blocks[3]}") .
                        $blocks[4] . $blocks[5] . $blocks[6] . $blocks[7]
                    ,
                    '_serial_hex' => str_replace(":", "", $sn->getHexValue()),
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
        if (isset($response['ont.gpon.autofind.password'])) {
            foreach ($response['ont.gpon.autofind.password']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['password'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.gpon.autofind.softwareVer'])) {
            foreach ($response['ont.gpon.autofind.softwareVer']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['fw_version'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.gpon.autofind.equipmentId'])) {
            foreach ($response['ont.gpon.autofind.equipmentId']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['equipment_id'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.gpon.autofind.checkCode'])) {
            foreach ($response['ont.gpon.autofind.checkCode']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['check_code'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.gpon.autofind.loid'])) {
            foreach ($response['ont.gpon.autofind.loid']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['loid'] = $this->convertHexToString($d->getHexValue());
            }
        }
        return $data;
    }

    /**
     * @param WrappedResponse[] $response
     * @return array
     */
    function getEponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.epon.autofind.ident'])) {
            if ($response['ont.epon.autofind.ident']->error()) {
                return [];
            }
            foreach ($response['ont.epon.autofind.ident']->fetchAll() as $ident) {
                $iface = $this->findIfaceByOid($ident->getOid());
                $data[$iface['id']] = [
                    'mac_address' => $ident->getHexValue(),
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
        if (isset($response['ont.epon.autofind.password'])) {
            foreach ($response['ont.epon.autofind.password']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['password'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.epon.autofind.softwareVer'])) {
            foreach ($response['ont.epon.autofind.softwareVer']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['fw_version'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.epon.autofind.equipmentId'])) {
            foreach ($response['ont.epon.autofind.equipmentId']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['equipment_id'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.epon.autofind.customInfo'])) {
            foreach ($response['ont.epon.autofind.customInfo']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['custom_info'] = $this->convertHexToString($d->getHexValue());
            }
        }
        if (isset($response['ont.epon.autofind.model'])) {
            foreach ($response['ont.epon.autofind.model']->fetchAll() as $d) {
                $iface = $this->findIfaceByOid($d->getOid());
                $data[$iface['id']]['model'] = $this->convertHexToString($d->getHexValue());
            }
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
        if($filter['sn_as_ascii']) {
            return array_map(function ($e) {
                if(isset($e['_serial_ascii'])) {
                    $e['serial'] = $e['_serial_ascii'];
                }
                return $e;
            }, $data);
        } else {
            return $data;
        }
    }
}

