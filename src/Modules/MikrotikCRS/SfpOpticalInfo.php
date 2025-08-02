<?php

namespace SwitcherCore\Modules\MikrotikCRS;

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
        $suffixOid = "";
        if ($params['interface']) {
            $suffixOid = ".{$this->parseInterface($params['interface'])['_snmp_id']}";
        }
        $oids = array_map(function ($e) use ($suffixOid) {
            return Oid::init($e->getOid() . $suffixOid);
        }, $this->oids->getOidsByRegex('^sfp.optical.*'));
        $response = $this->formatResponse($this->snmp->walk($oids));

        $RESPONSES = [];
        foreach ($response as $name => $resp) {
            $metricName = str_replace(['sfp_optical_'], '', Helper::fromCamelCase($name));
            foreach ($resp->fetchAll() as $value) {
                if ($resp->error()) continue;
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($value->getOid()));
                } catch (\Throwable $t) {
                    throw $t;
                }
                $RESPONSES[$iface['id']]['interface'] = $iface;
                $funcName = "_formula_".$metricName;
                $RESPONSES[$iface['id']][$metricName] =  $this->$funcName($value->getParsedValue());
            }
        }
        foreach ($RESPONSES as $id => $RESPONS) {
            if (!isset($RESPONS['temp'])) $RESPONSES[$id]['temp'] = null;
            if (!isset($RESPONS['vcc'])) $RESPONSES[$id]['vcc'] = null;
            if (!isset($RESPONS['tx_bias'])) $RESPONSES[$id]['tx_bias'] = null;
            if (!isset($RESPONS['tx_power'])) $RESPONSES[$id]['tx_power'] = null;
            if (!isset($RESPONS['rx_power'])) $RESPONSES[$id]['rx_power'] = null;
        }
        $this->response = array_values($RESPONSES);
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
