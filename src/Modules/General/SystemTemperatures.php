<?php


namespace SwitcherCore\Modules\General;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemTemperatures extends AbstractModule
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
            $response[$key] = (float)$val->getValue();
            $response['main'] = (float)$val->getValue();
            $response['main_from'] = $key;
        }

        return $response;
    }

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
        $returnedGets = $this->snmp->walk(
            array_map(function ($e) {return Oid::init($e->getOid()); }, $this->oids->getOidsByRegex('resources.temperature\..*'))
        );
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}

