<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class PonPowerAttenuation extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $interface = $this->parseInterface($params['interface']);

        if($interface['type'] !== 'ONU') {
            throw new \Exception("Allow only ONUs for current module");
        }

        $data = [
            'up' => [
                'olt_rx' => null,
                'onu_tx' => null,
                'attenuation' => null,
            ],
            'down' => [
                'olt_tx' => null,
                'onu_rx' => null,
                'attenuation' => null,
            ],
            'interface' => $interface,
        ];
        $lines = explode("\n", $this->telnet->exec("show pon power attenuation {$interface['name']}"));

        foreach ($lines as $line) {
            if(preg_match('/^(up|down)[ ]{1,}(Rx|Tx)[ ]{1,}?:(-?[0-9]{1,3}\.?[0-9]{1,}?\(dbm\)|(N\/A))[ ]{1,}(Rx|Tx).*?:(-?[0-9]{1,3}\.?[0-9]{1,}?\(dbm\)|(N\/A)).*?(-?[0-9]{1,3}\.?[0-9]{1,}?\(dB\)|(N\/A))$/', trim($line), $match)) {
                $data[$match[1]]['olt_' . strtolower($match[2])] = is_numeric(str_replace('(dbm)','', $match[3])) ? (float)str_replace('(dbm)','', $match[3]) : null;
                $data[$match[1]]['onu_' .strtolower($match[5])] = is_numeric(str_replace('(dbm)','', $match[6])) ? (float)str_replace('(dbm)','', $match[6]) : null;
                $data[$match[1]]['attenuation'] = is_numeric(str_replace('(dB)', '', $match[8])) ? (float)str_replace('(dB)', '', $match[8]) : null;
            }
        }
        $this->response = $data;
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