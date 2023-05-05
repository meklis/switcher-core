<?php

namespace SwitcherCore\Modules\ZTE\C300Series;

class OntDownHistory extends \SwitcherCore\Modules\ZTE\ModuleAbstract {
    public function run($params = [])
    {
        if(!$params['interface']) {
            throw new \Exception("Interface is required, must be ONT");
        }
        $interface = $this->parseInterface($params['interface']);
        $module = $this->getModule('zte_ont_info')->run(['interface' => $interface['id']])->getPretty();
        $data = [];
        if($module['data']['logs']) {
            foreach ($module['data']['logs'] as $log) {
                $regTime = null;
                if(isset($log['reg_time'])) {
                    $regTime = $log['reg_time'];
                }
                if(isset($log['authpath_time'])) {
                    $regTime = $log['authpath_time'];
                }
                $data[] = [
                    'down_reason' => $log['reason'],
                    'dereg_time' => $log['dereg_time'],
                    'reg_time' => $regTime,
                    'reg_since' => null,
                    'dereg_since' => null,
                ];
            }
        }
        $this->response = [['logs' => $data, 'interface' => $interface]];
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