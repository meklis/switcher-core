<?php

namespace SwitcherCore\Modules\CData\FD17xxV3;

use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class RawConsoleCommand extends CDataAbstractModuleFD17xxV3
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    public function run($params = [])
    {
        return $this->rawConsoleCommandRun($params);
    }

    protected function validResponse($response)
    {
        if (preg_match('/Command incomplete/', $response)) return false;
        if (preg_match('/Unknown command/', $response)) return false;
        if (preg_match('/Error/', $response)) return false;
        if (preg_match('/failed/i', $response)) return false;
        return true;
    }

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->response;
    }

}
