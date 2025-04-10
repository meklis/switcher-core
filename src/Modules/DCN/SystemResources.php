<?php


namespace SwitcherCore\Modules\DCN;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemResources extends AbstractModule
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
        if($this->response['switchinfo.switchCpuUsage']->error()) {
            throw new \Exception("Returned error {$this->response['switchinfo.switchCpuUsage']->error()} from {$this->response['switchinfo.switchCpuUsage']->getRaw()->ip}");
        }
        if($this->response['switchinfo.switchTemperature']->error()) {
            throw new \Exception("Returned error {$this->response['switchinfo.switchTemperature']->error()} from {$this->response['switchinfo.switchTemperature']->getRaw()->ip}");
        }
        if($this->response['switchinfo.switchMemoryUsage']->error()) {
            throw new \Exception("Returned error {$this->response['switchinfo.switchMemoryUsage']->error()} from {$this->response['switchinfo.switchMemoryUsage']->getRaw()->ip}");
        }

        $memory_util = (int) $this->getResponseByName('switchinfo.switchMemoryUsage')->fetchAll()[0]->getValue();
        $cpu_util = (int) $this->getResponseByName('switchinfo.switchCpuUsage')->fetchAll()[0]->getValue();
        $temperature = (int) $this->getResponseByName('switchinfo.switchTemperature')->fetchAll()[0]->getValue();

        return [
            'cpu' => [
                'util' => $cpu_util,
                '_temperature' => $temperature,
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' => null,
            'memory' => [
                'util' => $memory_util,
            ],
        ];
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids[] = $this->oids->getOidByName('switchinfo.switchCpuUsage')->getOid();
        $oids[] = $this->oids->getOidByName('switchinfo.switchTemperature')->getOid();
        $oids[] = $this->oids->getOidByName('switchinfo.switchMemoryUsage')->getOid();
        
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid, false);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oArray));
        return $this;
    }
}

