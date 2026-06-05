<?php

namespace SwitcherCore\Modules\CData\FD17xxV3;

use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;

class MultiRawConsoleCommand extends CDataAbstractModuleFD17xxV3
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
