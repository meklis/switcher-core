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
        $val = $this->response['physical.sensor.values']->fetchOne();
        $response = [
            'main' => $val->getValue() / 10,
            'main_from' => 'cpu',
            'cpu' => $val->getValue() / 10,
            'board' => null,
            'sensor' => null,
        ];

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
        $returnedGets = $this->snmp->get(
            [
                Oid::init($this->oids->getOidByName('physical.sensor.values')->getOid() . ".100006001"),
            ]
        );
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}

