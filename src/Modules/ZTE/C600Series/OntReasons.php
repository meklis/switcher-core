<?php

namespace SwitcherCore\Modules\ZTE\C600Series;

class OntReasons extends ModuleAbstract {
    public function run($params = [])
    {
        if(!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        $module = $this->getModule('zte_ont_info')->run(['interface' => $interface['id']])->getPretty();
        $reasons = [
            'last_down_reason' => null,
            'last_dereg_since' => null,
            'last_dereg' => null,
            'last_reg_since' => null,
            'last_reg' => null,
        ];
        if(isset($module['data']['logs'])) {
            foreach ($module['data']['logs'] as $log) {
                $reasons['last_down_reason'] = $log['reason'] ? $log['reason'] : $reasons['last_down_reason'];
                $reasons['last_dereg'] = $log['dereg_time'] !== '0000-00-00 00:00:00' ? $log['dereg_time'] : $reasons['last_dereg'];
                $reasons['last_reg'] = $log['authpath_time'] !== '0000-00-00 00:00:00' ? $log['authpath_time']  : $reasons['last_reg'] ;
            }
        }
        $reasons['interface'] = $interface;
        $this->response = [$reasons];
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