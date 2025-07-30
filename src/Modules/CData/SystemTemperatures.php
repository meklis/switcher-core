<?php


namespace SwitcherCore\Modules\CData;


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
        $cpuTemp = $this->getResponseByName('sensors.temperature.cpu')->fetchOne()->getValue() / 10;
        $response = [
            'main' => $cpuTemp,
            'main_from' => 'cpu',
            'cpu' => $cpuTemp,
            'board' => null,
            'cards' => [],
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
        $returnedGets = $this->snmp->get([
            Oid::init($this->oids->getOidByName('sensors.temperature.cpu')->getOid())
        ]);
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}

