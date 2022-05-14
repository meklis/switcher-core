<?php


namespace SwitcherCore\Modules\Dlink;

use Exception;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class ExecLineCtrl extends SwitchesPortAbstractModule
{

    protected $status = false;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $telnet;
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
        if(!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->status = false;
        try {
           $response = $this->telnet->exec($this->getCommandLine($params));
           if (preg_match('/(Success|Saving all configurations)/', $response) !== false) {
               $this->status = true;
           } else {
               throw new Exception("Error save configuration, response: " . $response);
           }
        } catch (Exception $e) {
            throw new Exception("error execute command: {$e->getMessage()}", 1, $e);
        }
        return $this;
    }
    abstract function getCommandLine($params = []);
}