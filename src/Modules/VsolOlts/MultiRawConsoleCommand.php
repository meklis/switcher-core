<?php

namespace SwitcherCore\Modules\VsolOlts;

class MultiRawConsoleCommand extends VsolOltsAbstractModule
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
