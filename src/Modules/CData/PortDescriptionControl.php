<?php

namespace SwitcherCore\Modules\CData;

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
        if (strlen($description) > 16) {
            throw new \InvalidArgumentException("Port name. <S><Length 1~16>.");
        }
        preg_match_all('!\d+!', $interface['name'], $matches);
        $f_s = $matches[0][0] . '/' . $matches[0][1];
        $port_numb = $matches[0][2];
        $this->console->exec("interface {$interface['pontype']} {$f_s}");
        $this->console->exec("port-name {$port_numb} {$description}");
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