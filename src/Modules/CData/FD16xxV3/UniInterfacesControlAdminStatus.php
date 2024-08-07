<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class UniInterfacesControlAdminStatus extends CDataAbstractModuleFD16xxV3
{
    protected $response = null;


    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return true; // TODO: Change the autogenerated stub
    }


    function getPretty()
    {
        return true;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $iface = $this->parseInterface($filter['interface']);

        $commands = [
            'interface gpon 0/0',
        ];
        switch ($filter['state']) {
            case 'enable':
                $commands[] = "ont port attribute {$iface['_port']} {$iface['_onu']} eth {$filter['num']} operational-state enable";
                break;
            case 'disable':
                $commands[] = "ont port attribute {$iface['_port']} {$iface['_onu']} eth {$filter['num']} operational-state disable";
                break;
        }
        $responses = $this->getModule('multi_console_command')->run([
            'commands' => $commands,
            'break_on_error' => 'yes',
        ])->getPrettyFiltered([]);
        if (count(array_filter($responses, function ($response) {
                return !$response['success'];
            })) != 0) {
            throw new Exception("Error running commands for change UNI port state", 1);
        }

        $this->response = true;
        return $this;
    }
}

