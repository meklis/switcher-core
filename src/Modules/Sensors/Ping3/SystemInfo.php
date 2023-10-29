<?php

namespace SwitcherCore\Modules\Sensors\Ping3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;

class SystemInfo extends AbstractModule
{
    public function run($params = [])
    {
        $oids = array_map(function ($o) {
            return Oid::init($o->getOid());
        }, [
            $this->oids->getOidByName('sys.deviceInfo'),
            $this->oids->getOidByName('sys.macAddr'),
            $this->oids->getOidByName('sys.Uptime'),
            $this->oids->getOidByName('sys.Name'),
            $this->oids->getOidByName('sys.Description'),
            ]
        );
        $this->response = $this->formatResponse($this->snmp->get($oids));
        return $this;
    }

    public function getPretty()
    {
        $data = [
            'descr' => $this->getResponseByName('sys.Description')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValueAsTimeTicks(),
            'uptime_sec' => $this->getResponseByName('sys.Uptime')->fetchAll()[0]->getValue(),
            'contact' => null,
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            '_info' =>  $this->getResponseByName('sys.deviceInfo')->fetchOne()->getValue(),
            'location' => null,
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
            'mac_addr' => $this->getResponseByName('sys.macAddr')->fetchOne()->getHexValue(),
        ];

        return $data;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}