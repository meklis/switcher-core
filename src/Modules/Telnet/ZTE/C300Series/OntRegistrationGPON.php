<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class OntRegistrationGPON extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        /**
        conf t
        interface gpon-olt_1/2/1
        onu 1 type 1GE sn HWTCB3D795FA (1GE для 1 портовых, ZTE-F660 для 4х портовых)
        onu 1 profile line 500mb remote One/VID/1263
         */
        $this->response = [];
        $this->exec("conf t");
        $this->exec("interface {$params['interface']}");
        $this->exec("onu {$params['number']} type {$params['type']} sn {$params['serial']}");
        $this->exec("onu {$params['number']} profile line {$params['profile_line']} remote {$params['profile_remote']} ");
        $this->exec("end");
        return $this;
    }

    public function getPretty()
    {
        return true;
    }

    public function getPrettyFiltered($filter = [])
    {
        return true;
    }

}