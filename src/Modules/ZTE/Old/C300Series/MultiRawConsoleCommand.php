<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;



use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

class MultiRawConsoleCommand extends ModuleAbstract
{
    public function run($params = [])
    {
        if(!isset($params['commands'])) {
            throw new \Exception("Commands parameter is required");
        }
        if(!is_array($params['commands']) && is_string($params['commands'])) {
            $commands = explode("\n", $params['commands']);
        } else {
            $commands = $params['commands'];
        }
        $response = [];
        foreach ($commands as $command) {
            $resp = $this->getModule('console_command')->run(['command' => trim($command)])->getPretty();
            $response[] = $resp;
            if(!$resp['success'] && $params['break_on_error'] == 'yes') {
                break;
            }
        }
        $this->response = $response;
        $this->getModule('zte_onu_state_by_interface')->cached = [];
        $this->getModule('zte_interface_running_config')->cached = [];
        
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}
