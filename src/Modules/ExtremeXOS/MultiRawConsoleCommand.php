<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use SwitcherCore\Modules\AbstractModule;

class MultiRawConsoleCommand extends AbstractModule
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
            if(preg_match("/\<\s*?exception *?['\"](.*)['\"].*?\>/", $command, $match)) {
                throw new \Exception($match[1]);
            }
            if(preg_match('/\<\s*?sleep *?([0-9]{1,3}).*?\>/', $command, $match)) {
                sleep($match[1]);
            }
            $resp = $this->getModule('console_command')->run(['command' => trim($command)])->getPretty();
            $response[] = $resp;
            if(!$resp['success'] && $params['break_on_error'] == 'yes') {
                break;
            }
        }
        $this->response = $response;

        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->response;
    }
}