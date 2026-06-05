<?php

namespace SwitcherCore\Modules\BDcom;

class MultiRawConsoleCommand extends BDcomAbstractModule
{
    public function run($params = [])
    {
        return $this->multiRawConsoleCommandRun($params);
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
