<?php

namespace SwitcherCore\Modules\CData\FD11XX;

use SwitcherCore\Switcher\Console\ConsoleInterface;

class PortDescriptionControl extends CDataAbstractModule
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    function setPortNameViaConsole($interface, $description)
    {
        /*
            SKL_OLT_8P# swport ge15
            SKL_OLT_8P(GE-15)# description
            <info-string>        - info-string,{MAX 64 Chars}
            SKL_OLT_8P(GE-15)# description test_descr_info
            Current Switch Port can't be configured!
        */
        $this->console->exec("swport ge{$interface['id']}");
        $response = $this->console->exec("description {$description}");

        if (preg_match('/Command incomplete/', $response)) return false;
        if (preg_match('/Unknown command/', $response)) return false;
        if (preg_match('/Error/', $response)) return false;
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