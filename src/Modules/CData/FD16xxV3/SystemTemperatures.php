<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

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
     * @throws \Exception
     */
    public function run($filter = [])
    {

        $returnedGets = $this->formatResponse($this->snmp->get(
            array_map(function ($e) {return Oid::init($e->getOid()); }, $this->oids->getOidsByRegex('sensors\.temperature\..*'))
        ));

        $this->response = $returnedGets;
        return $this;
    }
}