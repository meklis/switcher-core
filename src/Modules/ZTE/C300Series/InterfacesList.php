<?php


namespace SwitcherCore\Modules\ZTE\C300Series;




use SwitcherCore\Modules\ZTE\ModuleAbstract;

class InterfacesList extends ModuleAbstract
{
    public function run($params = [])
    {
        $response  = [];
        if($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            if($interface['type'] === 'ONU') {
                $interfacesForFilter = $this->getChildrenInterfaces($interface['parent']);
            } else {
                $interfacesForFilter = $this->getRootInterfaces();
            }
            $response = array_filter($interfacesForFilter, function ($e) use ($interface) {
                return $interface['id'] == $e['id'];
            });
        } elseif ($params['parent']) {
            $response = $this->getChildrenInterfaces($params['parent']);
        } elseif ($params['root']) {
           $response = $this->getRootInterfaces();
        } else {
            $response = $this->getRootInterfaces();
            foreach ($response as $resp) {
                try {
                    $response = array_merge($response, $this->getChildrenInterfaces($resp['name']));
                } catch (\Exception $e) {
                }
            }
        }
        $this->response = $response;
        return  $this;
    }
    protected function getChildrenInterfaces($interface) {
        $interface = $this->parseInterface($interface);
        try {
            $moduleResponse = $this->getModule('zte_onu_state_by_interface')->run(['interface' => $interface['name']])->getPretty();
        } catch (\Exception $e) {
            if(preg_match('/related information to show/', $e->getMessage())) {
                return  [];
            }
            throw $e;
        }
        $response = [];
        foreach ($moduleResponse['data'] as $data) {
            $iface = $data['interface'];
            unset($data['interface']);
            $data['technology'] = $moduleResponse['type'];
            $response[] = [
                'id' => $iface['id'],
                'name' => $iface['name'],
                'parent' => $iface['parent'],
                'type' => $iface['type'],
                'meta' => $data,
            ];
        }
        return $response;
    }
    protected function getRootInterfaces() {
        $response = [];
        $cards = $this->getModule('card_list')->run()->getPretty();
        foreach ($cards as $card) {
            if(!$card['technology']) continue;
            for ($i = 1; $i <= $card['num_ports']; $i++) {
                $interface = $this->parseInterface("{$card['technology']}-olt_{$card['shelf']}/{$card['slot']}/$i");
                $response[] = [
                    'id' => $interface['id'],
                    'name' => $interface['name'],
                    'parent' => $interface['parent'],
                    'type' => $interface['type'],
                    'meta' => $interface,
                ];
            }
        }
        return $response;
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