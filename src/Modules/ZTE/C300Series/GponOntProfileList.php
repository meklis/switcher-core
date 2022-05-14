<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;

class GponOntProfileList extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if($cache = $this->getCache('onu_profile_list_' . $params['type'])) {
            $this->response = $cache;
            return  $this;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
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
        $this->setCache('onu_profile_list_' . $params['type'], $this->response, 300);
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