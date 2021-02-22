<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class OnuFullyInfo extends C300ModuleAbstract
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
        $fdb = $this->getModule('zte_fdb')->run(['interface' => $interface['name']])->getPretty();
        $signal = $this->getModule('zte_onu_signal_strength')->run(['interface' => $interface['name']])->getPretty();
        foreach ($fdb as $k=>$_) {
            unset($fdb[$k]['interface']);
        }
        if(count($signal) === 0) {
            $signal = null;
        } else {
            unset($signal[0]['interface']);
        }
        unset($signal[0]['interface']);
        $this->response = [
            'general' => $generalInfo['data'],
            'fdb' => $fdb,
            'signal' => $signal[0],
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