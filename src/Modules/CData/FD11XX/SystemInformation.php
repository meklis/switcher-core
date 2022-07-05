<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemInformation extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
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
            'serial_num' => $this->convertHexToString($this->getResponseByName('sys.serialNum')->fetchOne()->getHexValue()),
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

