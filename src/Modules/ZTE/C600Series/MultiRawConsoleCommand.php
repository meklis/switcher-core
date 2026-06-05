<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class MultiRawConsoleCommand extends ModuleAbstract
{
    public function run($params = [])
    {
        return $this->multiRawConsoleCommandRun($params);
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
