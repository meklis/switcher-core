<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class SfpOpticalInfo extends AbstractInterfaces
{
    use InterfacesTrait;

    protected function _formula_temp($e)
    {
        if($e == 0) return null;
        return (float)$e;
    }
    protected function _formula_vcc($e)
    {
        if($e == 0) return null;
        return (float)$e / 1000;
    }
    protected function _formula_tx_bias($e)
    {
        if($e == 0) return null;
        return (float)$e;
    }
    protected function _formula_tx_power($e)
    {
        if($e == 0) return null;
        return (float)$e / 100;
    }
    protected function _formula_rx_power($e)
    {
        if($e == 0) return null;
        return (float)$e / 100;
    }

    public function run($params = [])
    {
        $sensorOid = $this->oids->getOidByName('physical.sensor.values')->getOid();

        $oids = [];
        if($params['interface']) {
            $interfaces = [$this->parseInterface($params['interface'])];
        } else {
            $interfaces = $this->getInterfacesIds();
        }
        $sensorToInterfacesMapping = [];
        $sensorIdsNameMapping = [];
        foreach ($interfaces as $interface) {
            if(isset($interface['_sensor_ids']['temperature'])) {
                $sensorToInterfacesMapping[$interface['_sensor_ids']['temperature']] = $interface;
                $sensorIdsNameMapping[$interface['_sensor_ids']['temperature']] = 'temp';
                $oids[] = Oid::init($sensorOid . '.' . $interface['_sensor_ids']['temperature']);
            }
            if(isset($interface['_sensor_ids']['voltage'])) {
                $sensorToInterfacesMapping[$interface['_sensor_ids']['voltage']] = $interface;
                $sensorIdsNameMapping[$interface['_sensor_ids']['voltage']] = 'vcc';
                $oids[] =  Oid::init($sensorOid . '.' . $interface['_sensor_ids']['voltage']);
            }
            if(isset($interface['_sensor_ids']['tx'])) {
                $sensorToInterfacesMapping[$interface['_sensor_ids']['tx']] = $interface;
                $sensorIdsNameMapping[$interface['_sensor_ids']['tx']] = 'tx_power';
                $oids[] =  Oid::init($sensorOid . '.' . $interface['_sensor_ids']['tx']);
            }
            if(isset($interface['_sensor_ids']['rx'])) {
                $sensorToInterfacesMapping[$interface['_sensor_ids']['rx']] = $interface;
                $sensorIdsNameMapping[$interface['_sensor_ids']['rx']] = 'rx_power';
                $oids[] =  Oid::init($sensorOid . '.' . $interface['_sensor_ids']['rx']);
            }
        }

        $response = $this->formatResponse($this->snmp->get($oids));

        $result = [];
        foreach ($response['physical.sensor.values']->fetchAll() as $resp) {
            $id = Helper::getIndexByOid($resp->getOid());
            $interface = $sensorToInterfacesMapping[$id];
            $key = $sensorIdsNameMapping[$id];
            switch ($key) {
                case 'temp': $val = $resp->getValue() / 10 ; break;
                case 'vcc': $val = $resp->getValue() / 100 ; break;
                case 'rx_power': $val = round(10 * log10($resp->getValue() / 1000), 3); break;
                case 'tx_power': $val = round(10 * log10($resp->getValue() / 1000), 3); break;
                default: $val = $resp->getValue(); break;
            }
            if(is_nan($val)) {
                $val = 0.0;
            }

            $result[$interface['id']]['interface'] = $interface;
            $result[$interface['id']][$key] = (float)$val;
        }
        $this->response = array_values(array_map(function ($e) {
            if (!isset($e['temp']) || !is_finite($e['temp'])) $e['temp'] = null;
            if (!isset($e['vcc']) || !is_finite($e['vcc'])) $e['vcc'] = null;
            if (!isset($e['tx_bias']) || !is_finite($e['tx_bias'])) $e['tx_bias'] = null;
            if (!isset($e['tx_power']) || !is_finite($e['tx_power'])) $e['tx_power'] = null;
            if (!isset($e['rx_power']) || !is_finite($e['rx_power'])) $e['rx_power'] = null;
            return $e;
        }, $result));
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}
