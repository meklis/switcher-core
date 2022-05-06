<?php


namespace SwitcherCore\Modules\Dlink\System;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class DGS1100SystemResources extends SwitchesPortAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $response = [
            'cpu' => null,
            'memory' => null,
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
        ];

        try {
            $response['cpu'] = [
                'util' => $this->getResponseByName('agent.CpuUtilization5sec')->fetchAll()[0]->getValue(),
                '_util5sec' => $this->getResponseByName('agent.CpuUtilization5sec')->fetchAll()[0]->getValue(),
                '_util1min' => $this->getResponseByName('agent.CpuUtilization1min')->fetchAll()[0]->getValue(),
                '_util5min' => $this->getResponseByName('agent.CpuUtilization5min')->fetchAll()[0]->getValue(),
            ];
        } catch (\Throwable $e) {
            $this->logger->warning($e);
        }

        try {
            $response['memory'] = [
                'util' => $this->getResponseByName('agent.DramUtilization5sec')->fetchAll()[0]->getValue(),
                '_util5sec' => $this->getResponseByName('agent.DramUtilization5sec')->fetchAll()[0]->getValue(),
                '_util1min' => $this->getResponseByName('agent.DramUtilization1min')->fetchAll()[0]->getValue(),
                '_util5min' => $this->getResponseByName('agent.DramUtilization5min')->fetchAll()[0]->getValue(),
            ];
        } catch (\Throwable $e) {
            $this->logger->warning($e);
        }

        return $response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oArray = [];
        if (!$filter['load_only'] || str_contains($filter['load_only'], 'cpu')) {
            foreach ($this->oids->getOidsByRegex('^agent\.Cpu.*') as $oid) {
                $oArray[] = Oid::init($oid->getOid());
            }
        }
        if (!$filter['load_only'] || str_contains($filter['load_only'], 'memory')) {
            foreach ($this->oids->getOidsByRegex('^agent\.Dram.*') as $oid) {
                $oArray[] = Oid::init($oid->getOid());
            }
        }
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

