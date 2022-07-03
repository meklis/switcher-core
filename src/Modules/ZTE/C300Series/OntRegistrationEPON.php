<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class OntRegistrationEPON extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        /**
         * conf t
        interface epon-olt_0/1/1
        onu 1 type  1GE_EPON mac 0000.0000.0000

         */
        $this->response = [];
        $response['mac'] = $this->macTo3octets($params['mac']);
        $this->exec("conf t");
        $this->exec("interface {$params['interface']}");
        $this->exec("onu {$params['number']} type {$params['type']} mac {$params['mac']}");
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