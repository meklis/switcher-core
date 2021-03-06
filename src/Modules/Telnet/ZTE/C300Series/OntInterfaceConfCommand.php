<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class OntInterfaceConfCommand extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = [];
        $this->exec("conf t");
        $this->exec("interface {$params['interface']}");
        $this->exec("{$params['command']}");
        $this->exec("end");
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