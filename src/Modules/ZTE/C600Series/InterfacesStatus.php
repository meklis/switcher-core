<?php


namespace SwitcherCore\Modules\ZTE\C600Series;




use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class InterfacesStatus extends ModuleAbstract
{
    public function run($params = [])
    {
        $interfaces = $this->getModule('interfaces_list')->run($params)->getPretty();
        $response = [];
        $runningConfigs = [];

        $parents = [];
        foreach ($interfaces as $interface) {
            if($interface['parent']) $parents[] = $interface['parent'];
            if($interface['type'] !== 'PON') {continue;}
            $runningConfig = $this->getModule('zte_interface_running_config')->run(['interface' => $interface['name']])->getPretty();
            foreach ($runningConfig as $conf) {
                $iFaceId = $conf['interface']['id'];
                unset($conf['interface']);
                $runningConfigs[$iFaceId] = $conf;
            }
        }
        foreach (array_unique($parents) as $parent) {
            $parentInterface = $this->parseInterface($parent);
            if($parentInterface['type'] !== 'PON') {continue;}
            $runningConfig = $this->getModule('zte_interface_running_config')->run(['interface' => $parentInterface['name']])->getPretty();
            foreach ($runningConfig as $conf) {
                $iFaceId = $conf['interface']['id'];
                unset($conf['interface']);
                $runningConfigs[$iFaceId] = $conf;
            }
        }
        foreach ($interfaces as $interface) {
            $meta=[];
            $meta['serial'] = null;
            $status = null;
            if(isset($interface['meta']['shelf'])) {
                $meta['shelf'] = $interface['meta']['shelf'];
            }
            if(isset($interface['meta']['slot'])) {
                $meta['slot'] = $interface['meta']['slot'];
            }
            if(isset($runningConfigs[$interface['id']])) {
                $conf = $runningConfigs[$interface['id']];
                $meta['type'] = $conf['type'];
                $meta['serial'] = $conf['serial'];
                $meta['profile_line'] = $conf['profile_line'];
                $meta['profile_remote'] = $conf['profile_remote'];
                $meta['mac'] = $conf['mac'];
            }
            if(isset($interface['meta']['technology'])) {
                if ($interface['meta']['technology'] === 'epon' && isset($interface['meta']['online_status'])) {
                    $status = $interface['meta']['online_status'];
                } else if (isset($interface['meta']['phase_state'])) {
                    $meta['phase_state'] = $interface['meta']['phase_state'];
                    $status = $interface['meta']['phase_state'] == 'Working' ? 'Online' : $interface['meta']['phase_state'];
                }
                $meta['technology'] = $interface['meta']['technology'];
            }
            $response[] = [
                'interface' => $interface,
                'meta' => $meta,
                'status' => $status,
                'address_learning' => null,
                'nway_status' => null,
                'nway_state' => null,
            ];
        }
        $this->response = $response;
        return  $this;
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