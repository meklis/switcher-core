<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemTemperatures extends \SwitcherCore\Modules\General\SystemTemperatures
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPrettyFiltered($filter = [])
    {
        $response = [
            'main' => null,
            'main_from' => null,
            'cpu' => null,
            'board' => null,
            'sensor' => null,
        ];
        foreach ($this->response as $rawOidName => $value) {
            if($value->error()) {
                throw new \SNMPException($value->error());
            }
            $key = str_replace("sensors.temperature.", "", $rawOidName);
            $val = $value->fetchOne();
            $response[$key] = (float)$val->getValue() / 100;
            $response['main'] = (float)$val->getValue()/ 100;
            $response['main_from'] = $key;
        }

        return $response;
    }

}

