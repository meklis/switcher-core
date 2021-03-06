<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class RebootOnu extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $interface = $this->parseInterface($params['interface']);
        if($interface['type'] !== 'ONU') {
            throw new \Exception("Reboot allow only for ONU");
        }

        $this->exec("conf t");
        $this->exec("pon-onu-mng {$interface['name']}");
        $this->exec("reboot");
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