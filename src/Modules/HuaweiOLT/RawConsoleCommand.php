<?php


namespace SwitcherCore\Modules\HuaweiOLT;




use SwitcherCore\Switcher\Console\ConsoleInterface;

class RawConsoleCommand extends HuaweiOLTAbstractModule
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
        if(preg_match('/Failure/', $response)) return  false;
        if(preg_match('/% .*error/', $response)) return  false;
        if(preg_match('/Unknown command/', $response)) return  false;
        return  true;
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
