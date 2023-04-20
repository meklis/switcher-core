<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class RebootOnu extends ModuleAbstract
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
        try {
            $this->telnet->write("reboot");
            $this->telnet->waitPrompt("Confirm to reboot?");
            $this->telnet->exec("yes");
        } catch (\Exception $e) {

        }
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