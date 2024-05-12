<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UnregisteredOnts extends CDataAbstractModule
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
        $response = $this->formatResponse($this->snmp->walk($oids));
        if($this->model->getExtraParamByName('pon_type') === 'EPON') {
            $this->response = array_values($this->getEponUnregisteredFromResponses($response));
        } elseif ($this->model->getExtraParamByName('pon_type') === 'GPON') {
            $this->response = array_values($this->getGponUnregisteredFromResponses($response));
        }
        return $this;
    }

    function getGponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.autofind.ident'])) {
            if ($response['ont.autofind.ident']->error()) {
                return [];
            }
            foreach ($response['ont.autofind.ident']->fetchAll() as $onuId => $sn) {
                $uniqId = Helper::getIndexByOid($sn->getOid());
                $ifacePort = explode(":", $this->parseInterface($uniqId)['name'])[0];
                $iface = $this->parseInterface($ifacePort . ":" . ($onuId + 1));
                $val = $sn->getValue();
                $hexVal = strtoupper(bin2hex(substr($val, 0, 4))) . substr($val, 5);
                $data[$uniqId] = [
                    '_serial_ascii' => $sn->getValue(),
                    '_serial_hex' => $hexVal,
                    'serial' => str_replace("-", "", $sn->getValue()),
                    '_ident' => str_replace("-", "", $sn->getValue()),
                    'interface' => $iface,
                    'password' => null,
                    'version' => null,
                    'equipment_id' => null,
                    'fw_version' => null,
                    'check_code' => null,
                    'loid' => null,
                    'reg_time' => null,
                    'model' => null,
                    'type' => 'gpon',
                ];
            }
        }
        foreach ($response['ont.autofind.equipmentId']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['equipment_id'] = $this->convertHexToString($d->getHexValue());
        }
        foreach ($response['ont.autofind.password']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['password'] = $this->convertHexToString($d->getHexValue());
        }
        foreach ($response['ont.autofind.softwareVer']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['fw_version'] = $this->convertHexToString($d->getHexValue());
        }
        return $data;
    }

    function getEponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.autofind.ident'])) {
            if ($response['ont.autofind.ident']->error()) {
                return [];
            }
            foreach ($response['ont.autofind.ident']->fetchAll() as $onuId => $sn) {
                $uniqId = Helper::getIndexByOid($sn->getOid());
                $ifacePort = explode(":", $this->parseInterface($uniqId)['name'])[0];
                $iface = $this->parseInterface($ifacePort . ":" . ($onuId + 1));
                $data[$uniqId] = [
                    '_mac_address_hex' => str_replace(":", "", $sn->getHexValue()),
                    'mac_address' => $sn->getHexValue(),
                    '_ident' => $sn->getHexValue(),
                    'interface' => $iface,
                    'password' => null,
                    'version' => null,
                    'equipment_id' => null,
                    'fw_version' => null,
                    'check_code' => null,
                    'loid' => null,
                    'reg_time' => null,
                    'model' => null,
                    'type' => 'epon',
                ];
            }
        }
        foreach ($response['ont.autofind.password']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['password'] = $this->convertHexToString($d->getHexValue());
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }
}

