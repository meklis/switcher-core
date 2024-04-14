<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


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
            'descr' => $this->getResponseByName('sys.Descr')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValueAsTimeTicks(),
            'uptime_sec' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValue(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'mac_addr' => null,
            'vendor_name' => null,
            'serial_num' => null,
            'board_software_ver' => $this->getResponseByName('sys.boardSoftwareVer')->fetchOne()->getValue(),
            'board_hardware_ver' => $this->getResponseByName('sys.boardHardwareVer')->fetchOne()->getValue(),
            'meta' =>  [
                'key' => $this->model->getKey(),
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
                ]
        ];
        if(!$this->getResponseByName('sys.macAddr')->error()) {
            $data['mac_addr'] = str_replace(' ', ':', $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue());
        }
        if(!$this->getResponseByName('sys.serialNum')->error()) {
            $data['serial_num'] = $this->getResponseByName('sys.serialNum')->fetchOne()->getValue();
        }
        if(!$this->getResponseByName('sys.vendorName')->error()) {
            $data['vendor_name'] =  $this->getResponseByName('sys.vendorName')->fetchOne()->getValue();
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

