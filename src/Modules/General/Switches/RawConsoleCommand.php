<?php

namespace SwitcherCore\Modules\General\Switches;

use \Exception;
use SwitcherCore\Switcher\Console\ConsoleInterface;
use SwitcherCore\Modules\AbstractModule;

class RawConsoleCommand extends AbstractModule {
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    public function run($params = []) {
        if (!$this->console) {
            throw new Exception("Module required telnet connection");
        }
        if (!isset($params['command'])) {
            throw new Exception("Command parameter is required");
        }
        $response = $this->console->exec($params['command']);
        $this->response = [
            'command' => $params['command'],
            'output' => $response,
            'success' => $this->validResponse($response),
        ];
        return $this;
    }

    protected function validResponse($response) {
        if (preg_match('/Invalid/', $response)) return false;
        if (preg_match('/Set unsuccessfully/', $response)) return false;
        return true;
    }

    function getPretty() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        return $this->response;
    }
}