<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


abstract class ExecLineCtrl extends AbstractModule
{

    protected $status = false;
    function getPretty()
    {
        return $this->status;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->status;
    }

    public function run($params = [])
    {
        if(!$this->telnet_conn) {
            throw new \Exception("Module required telnet connection");
        }
        $this->status = false;
        try {
           $response = $this->telnet_conn->exec($this->getCommandLine($params));
           if (preg_match('/(Success|Saving all configurations)/', $response) !== false) {
               $this->status = true;
           } else {
               throw new \Exception("Error save configuration, response: " . $response);
           }
        } catch (\Exception $e) {
            throw new \Exception("Error execute command", 1, $e);
        }
        return $this;
    }
    abstract function getCommandLine($params = []);
}