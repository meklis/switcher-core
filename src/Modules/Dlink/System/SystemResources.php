<?php


namespace SwitcherCore\Modules\Dlink\System;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemResources extends SwitchesPortAbstractModule
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

        if (!$filter['load_only'] || str_contains($filter['load_only'], 'memory')) {
            $response['memory'] = [
                'util' => $this->getResponseByName('agent.DramUtilization')->fetchOne()->getValue(),
                '_used' => $this->getResponseByName('agent.DramUtilizationUsedDRAM')->fetchOne()->getValue(),
                '_total' => $this->getResponseByName('agent.DramUtilizationTotalDRAM')->fetchOne()->getValue(),
            ];
        }

        if (!$filter['load_only'] || str_contains($filter['load_only'], 'interfaces')) {
            $response['interfaces'] = [];
            foreach ($this->getResponseByName('agent.PortUtilizationUtil')->fetchAll() as $resp) {
                $index = Helper::getIndexByOid($resp->getOid());
                $response['interfaces'][$index]['util'] = $resp->getValue();
            }
            foreach ($this->getResponseByName('agent.PortUtilizationTx')->fetchAll() as $resp) {
                $index = Helper::getIndexByOid($resp->getOid());
                $response['interfaces'][$index]['util_tx'] = $resp->getValue();
            }
            foreach ($this->getResponseByName('agent.PortUtilizationRx')->fetchAll() as $resp) {
                $index = Helper::getIndexByOid($resp->getOid());
                $response['interfaces'][$index]['util_rx'] = $resp->getValue();
            }
            if (count($response['interfaces']) > 0) {
                $indexes = $this->getIndexes();
                foreach ($response['interfaces'] as $index => $data) {
                    if (!isset($indexes[$index])) {
                        unset($response['interfaces'][$index]);
                    } else {
                        $response['interfaces'][$index]['interface'] = $indexes[$index];
                    }
                }
                $response['interfaces'] = array_values($response['interfaces']);
            } else {
                $response['interfaces'] = null;
            }
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
            $returnedGets = $this->snmp->walk($oArray);
        } else {
            $returnedGets = [];
        }
        if (!$filter['load_only'] || str_contains($filter['load_only'], 'interfaces')) {
            $returnedWalks = $this->snmp->walk([
                Oid::init($this->oids->getOidByName('agent.PortUtilizationTx')->getOid()),
                Oid::init($this->oids->getOidByName('agent.PortUtilizationRx')->getOid()),
                Oid::init($this->oids->getOidByName('agent.PortUtilizationUtil')->getOid()),
            ]);
        } else {
            $returnedWalks = [];
        }
        $this->response = $this->formatResponse(array_merge($returnedGets, $returnedWalks));
        return $this;
    }
}

