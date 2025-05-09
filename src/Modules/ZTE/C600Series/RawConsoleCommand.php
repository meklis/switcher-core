<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class RawConsoleCommand extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        if(!isset($params['command'])) {
            throw new \Exception("Command parameter is required");
        }
        
        if(isset($params['prompt'])) {
            $response = $this->telnet->exec($params['command'], true, $params['prompt']);
        } else {
            $response = $this->telnet->exec($params['command']);
        }

        $this->response = [
            'command' => $params['command'],
            'output' => $response,
            'success' => $this->validResponse($response),
        ];
        return $this;
    }
    protected function validResponse($response) {
        if(preg_match('/\[Successful\]/', $response)) return  true;
        if(preg_match('/\[OK\]/', $response)) return  true;
        if(preg_match('/Invalid input detected/', $response)) return  false;
        if(preg_match('/Failed/', $response)) return  false;
        return  true;
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