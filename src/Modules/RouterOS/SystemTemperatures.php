<?php


namespace SwitcherCore\Modules\RouterOS;


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
            'main' => $this->getResponseByName('resources.temperature.cpu')->fetchAll()[0]->getValue() / 10,
            'main_from' => 'cpu',
            'cpu' => $this->getResponseByName('resources.temperature.cpu')->fetchAll()[0]->getValue() / 10,
            'board' => null,
            'cards' => null,
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
            Oid::init($this->oids->getOidByName('resources.temperature.cpu')->getOid())
        ]);
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}

