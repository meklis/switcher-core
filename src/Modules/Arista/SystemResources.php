<?php


namespace SwitcherCore\Modules\Arista;


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
        if($this->response['hrDevice.hrProcessorLoad']->error()) {
            throw new \Exception("Returned error {$this->response['hrDevice.hrProcessorLoad']->error()} from {$this->response['hrDevice.hrProcessorLoad']->getRaw()->ip}");
        }
        if($this->response['entitySensorMIB.entPhySensorValue']->error()) {
            throw new \Exception("Returned error {$this->response['entitySensorMIB.entPhySensorValue']->error()} from {$this->response['entitySensorMIB.entPhySensorValue']->getRaw()->ip}");
        }

        if($this->response['hrStorage.hrStorageSize']->error()) {
            throw new \Exception("Returned error {$this->response['hrStorage.hrStorageSize']->error()} from {$this->response['hrStorage.hrStorageSize']->getRaw()->ip}");
        }
        $total_ram = (int) $this->getResponseByName('hrStorage.hrStorageSize')->fetchAll()[0]->getValue();
        if($total_ram === 0) throw new \Exception("hrStorage.hrStorageSize (Total RAM) value = 0 on {$this->response['hrStorage.hrStorageSize']->getRaw()->ip}");
        
        if($this->response['hrStorage.hrStorageUsed.MemoryInUse']->error()) {
            if($this->response['hrStorage.hrStorageUsed.Total']->error()) {
                throw new \Exception("Returned error {$this->response['hrStorage.hrStorageUsed.Total']->error()} from {$this->response['hrStorage.hrStorageUsed.Total']->getRaw()->ip}");
            }
            if($this->response['hrStorage.hrStorageUsed.Buffers']->error()) {
                throw new \Exception("Returned error {$this->response['hrStorage.hrStorageUsed.Buffers']->error()} from {$this->response['hrStorage.hrStorageUsed.Buffers']->getRaw()->ip}");
            }
            if($this->response['hrStorage.hrStorageUsed.Cache']->error()) {
                throw new \Exception("Returned error {$this->response['hrStorage.hrStorageUsed.Cache']->error()} from {$this->response['hrStorage.hrStorageUsed.Cache']->getRaw()->ip}");
            }

            $used_total_ram = (int) $this->getResponseByName('hrStorage.hrStorageUsed.Total')->fetchAll()[0]->getValue();
            $buffers_ram = (int) $this->getResponseByName('hrStorage.hrStorageUsed.Buffers')->fetchAll()[0]->getValue();
            $cache_ram = (int) $this->getResponseByName('hrStorage.hrStorageUsed.Cache')->fetchAll()[0]->getValue();
            $free_ram = $total_ram - $used_total_ram + $buffers_ram + $cache_ram;
            $memory_util = round(100 - $free_ram / $total_ram * 100, 2);
        } else {            
            $used_ram = (int) $this->getResponseByName('hrStorage.hrStorageUsed.MemoryInUse')->fetchAll()[0]->getValue();
            $free_ram = $total_ram - $used_ram;
            $memory_util = round(100 - $free_ram / $total_ram * 100, 2);
        }

        $cpu_util = (int) $this->getResponseByName('hrDevice.hrProcessorLoad')->fetchAll()[0]->getValue();

        return [
            'cpu' => [
                'util' => $cpu_util,
                '_temperature' => null,
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
        $oids[] = $this->oids->getOidByName('hrDevice.hrProcessorLoad')->getOid();
        $oids[] = $this->oids->getOidByName('hrStorage.hrStorageSize')->getOid();
        $oids[] = $this->oids->getOidByName('hrStorage.hrStorageUsed.Total')->getOid();
        $oids[] = $this->oids->getOidByName('hrStorage.hrStorageUsed.MemoryInUse')->getOid();
        $oids[] = $this->oids->getOidByName('hrStorage.hrStorageUsed.Buffers')->getOid();
        $oids[] = $this->oids->getOidByName('hrStorage.hrStorageUsed.Cache')->getOid();
        
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid, false);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oArray));
        return $this;
    }
}

