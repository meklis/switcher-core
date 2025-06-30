<?php

namespace SwitcherCore\Modules\CData\FD5008;

class StatePortControl extends \SwitcherCore\Modules\General\Switches\StatePortControl {
    use InterfacesTrait;

    function run($params = []) {
        $interface = $this->parseInterface($params['interface']);

        $state = false;
        if($params['state'] == 'enable') {
            $state = 'no shutdown';
        } else if($params['state'] == 'disable') {
            $state = 'shutdown';
        }
        if(!$state) throw new \Exception("Param 'state' must be 'enable' or 'disable'");

        $output = $this->getModule('multi_console_command')
            ->run(['commands' => [
                'interface ' . $interface['_fullname'],
                $state,
                'exit',
                'save',
            ]])->getPretty();

        $resp = false;
        if(isset($output[3]['output']) && preg_match('/^Configuration file .*? had been saved successfully/', $output[3]['output'])) {
            $resp = true;
        }

        $this->response = [
            'interface' => $interface,
            'state' => $params['state'],
            'response' => $resp,
        ];
        return $this;
    }
}