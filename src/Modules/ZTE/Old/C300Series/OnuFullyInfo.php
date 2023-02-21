<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;



use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class OnuFullyInfo extends ModuleAbstract
{
    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if($interface['type'] !== 'ONU') {
            throw new \Exception("Only ONU allowed for current method");
        }
        $generalInfo = $this->getModule('zte_onu_info')->run(['interface' => $interface['name']])->getPretty();
        if($interface['technology'] === 'gpon') {
            $etherInfo = $this->getModule('zte_onu_ether_iface_info')->run(['interface' => $interface['name']])->getPretty();
        } else {
            $etherInfo = null;
        }
        $fdb = $this->getModule('fdb')->run(['interface' => $interface['name']])->getPretty();
        $signal = $this->getModule('zte_pon_power_attenuation')->run(['interface' => $interface['name']])->getPretty();
        foreach ($fdb as $k=>$_) {
            unset($fdb[$k]['interface']);
        }
        unset($signal['interface']);
        $this->response = [
            'general' => $generalInfo['data'],
            'fdb' => $fdb,
            'signal' => $signal,
            'ether' => $etherInfo,
            'interface' => $interface,
        ];
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