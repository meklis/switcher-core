<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemInformation extends AbstractModule
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
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValueAsTimeTicks(),
            'uptime_sec' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValue(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'meta' => [
                'key' => $this->model->getKey(),
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
            ]
        ];
        if ($this->cached) {
            $data['descr'] = $this->cached['descr'];
            $data['serial_num'] = $this->cached['serial_num'];
            $data['mac_addr'] = $this->cached['mac_addr'];
            $data['board_software_ver'] = $this->cached['board_software_ver'];
            $data['board_hardware_ver'] = $this->cached['board_hardware_ver'];
        } else {
            $data['descr'] = $this->getResponseByName('sys.Descr')->fetchOne()->getValue();
            $data['serial_num'] = $this->getResponseByName('sys.serialNum')->fetchOne()->getValue();
            $data['mac_addr'] = strtoupper($this->getResponseByName('sys.macAddress')->fetchOne()->getHexValue());
            $data['board_software_ver'] = $this->getResponseByName('sys.boardSoftwareVer')->fetchOne()->getValue();
            $data['board_hardware_ver'] = $this->getResponseByName('sys.boardHardwareVer')->fetchOne()->getValue();
            $this->setCache('system', $data, 3600);
        }

        return $data;
    }

    protected $cached = null;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = [
            $this->oids->getOidByName('sys.Uptime'),
            $this->oids->getOidByName('sys.Contact'),
            $this->oids->getOidByName('sys.Location'),
            $this->oids->getOidByName('sys.Name'),
        ];
        if ($cached = $this->getCache('system')) {
            $this->cached = $cached;
        } else {
            $oids[] = $this->oids->getOidByName('sys.Descr');
            $oids[] = $this->oids->getOidByName('sys.serialNum');
            $oids[] = $this->oids->getOidByName('sys.boardHardwareVer');
            $oids[] = $this->oids->getOidByName('sys.boardSoftwareVer');
            $oids[] = $this->oids->getOidByName('sys.macAddress');
        }
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid() . ".0", true);
        }
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

