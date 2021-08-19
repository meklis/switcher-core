<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemInformation extends AbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
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
        $data = [
            'cpu_usage' => null,
            'mem_total_size' => null,
            'mem_free_size' => null,
            'temperature' => null,
            'temperature_trashhold' => null,
            'descr' => $this->getResponseByName('sys.Descr')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchOne()->getValueAsTimeTicks(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'mac_addr' => str_replace(' ', ':', $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue()),
            'vendor_name' => $this->getResponseByName('sys.vendorName')->fetchOne()->getValue(),
            'serial_num' => $this->getResponseByName('sys.serialNum')->fetchOne()->getValue(),
            'board_software_ver' => $this->getResponseByName('sys.boardSoftwareVer')->fetchOne()->getValue(),
            'board_hardware_ver' => $this->getResponseByName('sys.boardHardwareVer')->fetchOne()->getValue(),
            'meta' =>  [
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
                ]
        ];
        if($this->model->getName() === 'C-Data FD1208S') {
            $data = array_merge($data, [
               'cpu_usage' => (int)$this->getResponseByName('sys.cpuUsage')->fetchOne()->getValue(),
               'mem_total_size' => (int)$this->getResponseByName('sys.memTotalSize')->fetchOne()->getValue(),
               'mem_free_size' => (int)$this->getResponseByName('sys.memFreeSize')->fetchOne()->getValue(),
               'temperature' => (float) ($this->getResponseByName('sys.temperature')->fetchOne()->getValue()) / 10,
               'temperature_trashhold' => (float)($this->getResponseByName('sys.temperatureTreshhold')->fetchOne()->getValue()) / 10,
            ]);
        }
        return $data;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = $this->oids->getOidsByRegex('^sys\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid() . ".0"  ,true);
        }
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

