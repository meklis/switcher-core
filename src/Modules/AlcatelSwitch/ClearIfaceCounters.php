<?php

namespace SwitcherCore\Modules\AlcatelSwitch;

use \Exception;
use \SwitcherCore\Modules\AbstractModule;

class ClearIfaceCounters extends AbstractModule {

    use InterfacesTrait;

    public function run($params = []) {
        if(!isset($params['interface'])) {
            throw new Exception('Parameter "interface" is not set');
        }
        $iface = $this->parseInterface($params['interface']);
        if(!$iface) throw new Exception("Interface {$params['interface']} not found");

        $this->response = $this->getModule('console_command')
            ->run(['command' => 'clear counters ethernet ' . $iface['name']])->getPretty();
        return $this;
    }

    public function getPretty() {
        if($this->response['success'] === true) {
            return true;
        }
        throw new Exception("Error when executing console_command");
    }

    public function getPrettyFiltered($filter = [], $fromCache = false) {
        return $this->getPretty();
    }
   
}