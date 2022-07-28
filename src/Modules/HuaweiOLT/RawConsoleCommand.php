<?php


namespace SwitcherCore\Modules\HuaweiOLT;



use Exception;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

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
        $command = $params['command'];
        if(strpos($command, "<cr>") !== false) {
            $command = trim(str_replace("<cr>", "", $command));
            $this->telnet->write($command);
            usleep(100000);
            $this->telnet->write("");
            $this->telnet->waitPrompt($this->telnet->getDeviceHelper()->getPrompt());
            $response = $this->telnet->getBuffer();
        } elseif (strpos($command, "<prompt=") !== false) {
            if(preg_match('/<prompt=(.*?)>/', $command, $m)) {
                $command = trim(preg_replace('/<prompt=(.*?)>/', '', $command));
                $this->telnet->write($command);
                usleep(300000);
                $this->telnet->write("{$m[1]}");
                $this->telnet->waitPrompt($this->telnet->getDeviceHelper()->getPrompt());
                $response = $this->telnet->getBuffer();
            } else {
                $this->response = [
                    'command' => $params['command'],
                    'output' => '',
                    'success' => "ERROR PARSE PROMPT",
                ];
            }
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
        if(preg_match('/Failure/', $response)) return  false;
        if(preg_match('/% .*error/', $response)) return  false;
        if(preg_match('/Unknown command/', $response)) return  false;
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