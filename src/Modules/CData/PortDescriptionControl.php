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

        if(!isset($interface['pontype'])){
            preg_match_all('![a-z]!', $interface['name'], $letters);
            $interface['pontype'] = implode('', $letters[0]);
        }

        $this->console->exec("interface {$interface['pontype']} {$f_s}");
        $response = $this->console->exec("port-name {$port_numb} {$description}");

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