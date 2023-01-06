<?php


namespace SwitcherCore\Modules\Dlink\System;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class DefaultParser extends SwitchesPortAbstractModule
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

        $data = [
            'descr' => $this->getResponseByName('sys.Descr')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValueAsTimeTicks(),
            'uptime_sec' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValue(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'meta' => [
                'key' => $this->model->getKey(),
                'type' => $this->model->getDeviceType(),
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
            ],
            'mac_addr' => '',
            'serial_num' => '',
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
    public function run($filter = [])
    {
        $oids = $this->oids->getOidsByRegex('^sys\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid() . ".0", true);
        }
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

