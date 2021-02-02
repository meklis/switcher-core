<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class GponOntProfileList extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = [];
        $input = $this->telnet->exec("show pon onu-profile gpon {$params['type']}");
        $lines = explode("\n", $input);
        $lines = array_splice($lines, 2);
        foreach ($lines as $k=>$line) {
            $lines[$k] = trim($line);
        }
        if($lines) {
            $this->response = $lines;
        }
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