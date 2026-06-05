<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use SwitcherCore\Modules\ZTE\ModuleAbstract;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class RawConsoleCommand extends ModuleAbstract
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    public function run($params = [])
    {
        return $this->rawConsoleCommandRun($params);
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
