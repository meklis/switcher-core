<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

class OntVendorInfo extends ModuleAbstract {
    public function run($params = [])
    {
        if(!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        $module = $this->getModule('zte_ont_info')->run(['interface' => $interface['id']])->getPretty();
        $data = [
            'interface' => $interface,
            'ver_hardware' => isset($module['data']['ver_hardware']) ? $module['data']['ver_hardware'] : null,
            'ver_software' => isset($module['data']['ver_software']) ? $module['data']['ver_software'] : null,
            'ver_firmware' => isset($module['data']['ver_firmware']) ? $module['data']['ver_firmware'] : null,
            'onu_type_reported' => isset($module['data']['onu_type_reported']) ? $module['data']['onu_type_reported'] : null,
        ];
        $this->response = [$data];
        return $this;
    }

    public function getPretty()
    {
       return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}