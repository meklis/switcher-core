<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class InterfaceRunningConfig extends ModuleAbstract
{
    public $cached = [];

    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $interface = $this->parseInterface($params['interface']);
        if ($interface['type'] !== 'PON') {
            throw new \Exception("Allow only PON-Port interfaces");
        }
        if (isset($this->cached[$interface['id']])) {
            $this->response = $this->cached[$interface['id']];
            return $this;
        }
        $lines = explode("\n", $this->telnet->exec("show running-config interface {$interface['name']}"));
        $data = [];
        foreach ($lines as $line) {
            if (preg_match('/^onu ([0-9]{1,}) type (.*?) sn (.*)$/', trim($line), $matches)) {
                $data[$matches[1]]['type'] = $matches[2];
                $data[$matches[1]]['serial'] = $matches[3];
            }
            if (preg_match('/^onu ([0-9]{1,}) profile line (.*?) remote (.*)$/', trim($line), $matches)) {
                $data[$matches[1]]['profile_line'] = $matches[2];
                $data[$matches[1]]['profile_remote'] = $matches[3];
            }
            if (preg_match('/^onu ([0-9]{1,}) type (.*?) mac (.*?) (.*)$/', trim($line), $matches)) {
                $data[$matches[1]]['type'] = $matches[2];
                $data[$matches[1]]['mac'] = $matches[3];
            }
        }
        $response = [];

        foreach ($data as $onuNum => $d) {

            $iface = $this->parseInterface($interface['id'] + $onuNum);
            $response[] = [
                'interface' => $iface,
                'type' => isset($d['type']) ? $d['type'] : null,
                'serial' => isset($d['serial']) ? $d['serial'] : null,
                'profile_line' => isset($d['profile_line']) ? $d['profile_line'] : null,
                'profile_remote' => isset($d['profile_remote']) ? $d['profile_remote'] : null,
                'mac' => isset($d['mac']) ? $d['mac'] : null,
            ];
        }

        $this->response = $response;
        $this->cached[$interface['id']] = $response;
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