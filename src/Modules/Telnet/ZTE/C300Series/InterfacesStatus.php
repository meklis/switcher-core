<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;




class InterfacesStatus extends C300ModuleAbstract
{
    public function run($params = [])
    {
        $interfaces = $this->getModule('interfaces_list')->run($params)->getPretty();
        $response = [];
        foreach ($interfaces as $k=>$interface) {
            $meta=[];
            $status = null;
            if(isset($interface['meta']['technology'])) {
                if ($interface['meta']['technology'] === 'epon' && isset($interface['meta']['online_status'])) {
                    $status = $interface['meta']['online_status'];
                } else if (isset($interface['meta']['phase_state'])) {
                    $meta['phase_state'] = $interface['meta']['phase_state'];
                    $status = $interface['meta']['state'] === 'enable' ? 'Online' : 'Offline';
                }
                $meta['technology'] = $interface['meta']['technology'];
            }
            unset($interface['meta']);
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