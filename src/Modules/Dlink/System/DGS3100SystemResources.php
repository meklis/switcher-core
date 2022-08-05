<?php


namespace SwitcherCore\Modules\Dlink\System;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class DGS3100SystemResources extends SwitchesPortAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPrettyFiltered($filter = [])
    {
        $response = [
            'cpu' => null,
            'memory' => null,
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
        ];
        if (!$filter['load_only'] || str_contains($filter['load_only'], 'cpu')) {
            $response['cpu'] = [
                'util' => $this->getResponseByName('agent.CpuUtilization1min')->fetchAll()[0]->getValue(),
                '_util5sec' => $this->getResponseByName('agent.CpuUtilization5sec')->fetchAll()[0]->getValue(),
                '_util1min' => $this->getResponseByName('agent.CpuUtilization1min')->fetchAll()[0]->getValue(),
                '_util5min' => $this->getResponseByName('agent.CpuUtilization5min')->fetchAll()[0]->getValue(),
            ];
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
        if ($oArray) {
            $returnedGets = $this->snmp->get($oArray);
        } else {
            $returnedGets = [];
        }

        $this->response = $this->formatResponse(array_merge($returnedGets));
        return $this;
    }
}

