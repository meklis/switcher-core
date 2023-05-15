<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class GponOntProfileList extends ModuleAbstract
{
    public function run($params = [])
    {
        $this->response = [
            'line' => $this->lineProfileList(),
            'remote' => $this->remoteProfileList(),
            'traffic' => $this->gponTrafficProfile(),
            'epon_sla' => $this->eponTrafficProfile(),
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

    function gponTrafficProfile() {
        if($cache = $this->getCache('gpon_traffic_profile')) {
            return $cache;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $input = $this->telnet->exec("show gpon profile traffic");
        $lines = explode("\n", $input);
        $profiles = [];
        foreach ($lines as $line) {
            if(preg_match('/^Name.*?:(.*)$/', trim($line), $m)) {
                $profiles[] = trim($m[1]);
            }
        }
        $this->setCache('gpon_traffic_profile', $profiles, 60);
        return $profiles;
    }

    function eponTrafficProfile() {
        if($cache = $this->getCache('epon_traffic_profile')) {
            return $cache;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $input = $this->telnet->exec("show epon onu-sla-profile");
        $lines = explode("\n", $input);
        $profiles = [];
        $listStarted = false;
        foreach ($lines as $line) {
            if(preg_match('/------/', trim($line))) {
                $listStarted = true;
                continue;
            }
            if($listStarted) {
                $profiles[] = trim($line);
            }
        }
        $this->setCache('epon_traffic_profile', $profiles, 60);
        return $profiles;
    }
}