<?php

namespace SwitcherCore\Modules\Raisecom;

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

        $resp = $this->getModule('multi_console_command')
            ->run(['commands' => [
                'config', 
                'interface ' . $iface['_fullname'], 
                'clear interface statistics',
            ], 'break_on_error' => 'yes'])->getPretty();

        $this->response = $resp;
        return $this;
    }

    public function getPretty() {
        if(isset($this->response[2]) && $this->response[2]['success'] === true 
        && preg_match('/Set successfully/', $this->response[2]['output'])) {
            return true;
        }
        throw new Exception("Error when executing multi_console_command: " . $this->response[2]['output']);
    }

    public function getPrettyFiltered($filter = [], $fromCache = false) {
        return $this->getPretty();
    }
   
}