<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class OnuSignalStrengthInfo extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = [
            'onu' => $params['onu'],
            'onu_rx' => $this->command("show pon power onu-rx {$params['onu']}"),
            'olt_rx' => $this->command("show pon power olt-rx {$params['onu']}"),
        ];
        return $this;
    }
    private function command($input) {
        $input = $this->telnet->exec($input);
        if (!$input) throw new \Exception("Empty response on command '$input'");
        $lines = explode("\n", $input);
        if(count($lines) < 3) {
            throw new \Exception("Unknown output - '$input'");
        }
        $lines = array_splice($lines, 2);
        if(preg_match('/^(.*?)[ ]{1,}(.*)$/', $lines[0], $matches)) {
            return str_replace('(dbm)', '', $matches[2]);
        }
        return null;
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