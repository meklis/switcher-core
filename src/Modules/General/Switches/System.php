<?php


namespace SwitcherCore\Modules\General\Switches;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

abstract class System extends AbstractInterfaces
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getPrettyFiltered($filter = []) {
        return $this->getPretty();
    }
    function getRaw() {
        return $this->response;
    }

    function getPretty() {
        $data = [
            'descr' => $this->getResponseByName('sys.Descr')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValueAsTimeTicks(),
            'uptime_sec' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValue(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'meta' =>  [
                'key' => $this->model->getKey(),
                'type' => $this->model->getDeviceType(),
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
            ],
            'serial_num' => '',
            'mac_addr' => '',
        ];

        try {
            $data['mac_addr'] = $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue();
        } catch (\Exception $e) {
        }

        try {
            $data['serial_num'] = $this->getResponseByName('sys.serialNum')->fetchOne()->getValue();
        } catch (\Exception $e) {
        }
        return $data;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = []) {
        $oids = $this->oids->getOidsByRegex('^sys\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid(),true);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oArray));
        return $this;
    }
}

