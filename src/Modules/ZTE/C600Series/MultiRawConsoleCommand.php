<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

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
            if(preg_match("/\<\s*?exception *?['\"](.*)['\"].*?\>/i", $command, $match)) {
                throw new \Exception($match[1]);
            }
            if(preg_match('/\<\s*?sleep *?([0-9]{1,3}).*?\>/i', $command, $match)) {
                sleep($match[1]);
                continue;
            }
            $prompt = null;
            if(preg_match("/^(.*)\<\s*?prompt *?['\"](.*)['\"].*?\>/i", $command, $match)) {
                $command = $match[1];
                $prompt = $match[2];
            }
            try {
                $resp = $this->getModule('console_command')->run(['command' => trim($command), 'prompt' => $prompt])->getPretty();
                $response[] = $resp;
            } catch (\Throwable $e) {
                $resp['success'] = false;
                $resp['command'] = $command;
                $resp['output'] = $this->telnet->getBuffer();
                $resp['_buffer'] = $this->telnet->getGlobalBuffer();
                $resp['_exception'] = $e->getMessage();
            }
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

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}
