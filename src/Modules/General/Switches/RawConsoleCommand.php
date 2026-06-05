<?php

namespace SwitcherCore\Modules\General\Switches;

use SwitcherCore\Switcher\Console\ConsoleInterface;
use SwitcherCore\Modules\AbstractModule;

class RawConsoleCommand extends AbstractModule {
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    public function run($params = []) {
        return $this->rawConsoleCommandRun($params);
    }

    protected function validResponse($response) {
        if (preg_match('/invalid/i', $response)) return false;
        if (preg_match('/Set unsuccessfully/i', $response)) return false;
        if (preg_match("/Ambiguous command .* marker/i", $response)) return false;
        return true;
    }

    function getPretty() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        return $this->response;
    }
}
