<?php


namespace SwitcherCore\Modules\Arista;


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
               continue;
            }
            $key = str_replace("resources.temperature.", "", $rawOidName);
            $val = $value->fetchOne();
            $response[$key] = (float)$val->getValue() / 10;
            $response['main'] = (float)$val->getValue() / 10;
            $response['main_from'] = $key;
        }

        return $response;
    }
}

