<?php

namespace SwitcherCore\Modules\EltexSwitch;

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
        $boardTemp = $this->getResponseByName('sensors.temperature.board')->fetchOne()->getValue();
        if($boardTemp === 'NULL') {
            $boardTemp = null;
        } else {
            $boardTemp = (float)$boardTemp;
        }
        $response = [
            'main' => $boardTemp,
            'main_from' => 'board',
            'board' => $boardTemp,
            'cpu' => null,
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
        $returnedGets = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('sensors.temperature.board')->getOid())
        ]);
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}