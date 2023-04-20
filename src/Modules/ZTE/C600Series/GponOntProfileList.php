<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class GponOntProfileList extends ModuleAbstract
{
    public function run($params = [])
    {
        $this->response = [
            'line' => $this->lineProfileList(),
            'remote' => $this->remoteProfileList(),
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

    function lineProfileList() {
        if($cache = $this->getCache('onu_profile_list_line')) {
            return  $cache;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $input = $this->telnet->exec("show pon onu-profile gpon line");
        $lines = explode("\n", $input);
        $lines = array_splice($lines, 2);
        foreach ($lines as $k=>$line) {
            $lines[$k] = trim($line);
        }
        $this->setCache('onu_profile_list_line' , $lines, 60);
        return $lines;
    }

    function remoteProfileList() {
        if($cache = $this->getCache('onu_profile_list_remote')) {
            return $cache;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $input = $this->telnet->exec("show pon onu-profile gpon remote");
        $lines = explode("\n", $input);
        $lines = array_splice($lines, 2);
        foreach ($lines as $k=>$line) {
            $lines[$k] = trim($line);
        }
        $this->setCache('onu_profile_list_remote', $lines, 60);
        return $lines;
    }
}