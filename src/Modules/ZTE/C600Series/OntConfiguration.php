<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

class OntConfiguration extends ModuleAbstract {
    public function run($params = [])
    {
        if(!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        $module = $this->getModule('zte_ont_info')->run(['interface' => $interface['id']])->getPretty();
        $data = [
          'type' => null,
          'state' => null,
          'password' => null,
          'current_channel' => null,
          'auth_mode' => null,
          'sn_bind' => null,
          'vport_mode' => null,
          'dba_mode' => null,
          'line_profile' => null,
          'service_profile' => null,
          'online_duration' => null,
          'fec' => null,
          'fec_actual_mode' => null,
          'pps1_tod' => null,
          'auto_replace' => null,
          'mcast_encrypt' => null,
          'type_configured' => null,
          'type_reported' => null,
          'auth_info' => null,
        ];
        foreach ($data as $key=>$val) {
            if(isset($module['data'][$key])) {
                $data[$key] = $module['data'][$key];
            }
        }
        $data['interface'] = $interface;
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