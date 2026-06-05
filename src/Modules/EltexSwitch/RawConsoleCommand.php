<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SwitcherCore\Switcher\Console\ConsoleInterface;
use SwitcherCore\Modules\AbstractModule;

class RawConsoleCommand extends AbstractModule
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
        if (preg_match('/Incomplete/', $response)) return false;
        if (preg_match('/bad parameter/', $response)) return false;
        if (preg_match('/invalid/', $response)) return false;
        if (preg_match('/Unrecognized/', $response)) return false;
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
