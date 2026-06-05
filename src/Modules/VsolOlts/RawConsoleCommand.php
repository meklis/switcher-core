<?php

namespace SwitcherCore\Modules\VsolOlts;

use SwitcherCore\Switcher\Console\ConsoleInterface;

class RawConsoleCommand extends VsolOltsAbstractModule
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
        if (preg_match('/% There is no matched command./', $response)) return false;
        if (preg_match('/% Unknown command./', $response)) return false;
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
