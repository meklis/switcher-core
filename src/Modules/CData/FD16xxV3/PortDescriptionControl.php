<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

use SwitcherCore\Switcher\Console\ConsoleInterface;

class PortDescriptionControl extends CDataAbstractModuleFD16xxV3
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    function setPortNameViaConsole($interface, $description)
    {

        $this->console->exec("interface gpon 0/0");
        $response = $this->console->exec("port-desc {$interface['_port']} description {$description}");

        if (preg_match('/Command incomplete/', $response)) return false;
        if (preg_match('/Unknown command/', $response)) return false;
        return true;
    }

    public function run($params = [])
    {
        $interface = $this->parseInterface($params['interface']);
        if ($interface['type'] == 'ONU') {
            throw new \InvalidArgumentException("ONU not allowed, just physical ports");
        }

        $description = str_replace(' ', '_', $params['description']);

        $this->response = $this->setPortNameViaConsole($interface, $description);;
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }
}