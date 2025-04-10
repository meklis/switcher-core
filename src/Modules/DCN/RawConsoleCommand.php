<?php

namespace SwitcherCore\Modules\DCN;

use Exception;
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
            throw new \Exception("Module required telnet/ssh connection");
        }
        if (!isset($params['command'])) {
            throw new \Exception("Command parameter is required");
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
        return true;
    }

    function getPretty() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        return $this->response;
    }
}