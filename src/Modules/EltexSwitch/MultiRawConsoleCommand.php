<?php

namespace SwitcherCore\Modules\EltexSwitch;

use SwitcherCore\Modules\AbstractModule;

class MultiRawConsoleCommand extends AbstractModule
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
