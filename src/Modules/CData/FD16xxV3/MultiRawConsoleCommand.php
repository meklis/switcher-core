<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

class MultiRawConsoleCommand extends CDataAbstractModuleFD16xxV3
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
