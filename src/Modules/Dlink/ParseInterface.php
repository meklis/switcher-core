<?php

namespace SwitcherCore\Modules\Dlink;

class ParseInterface extends SwitchesPortAbstractModule
{
    public function run($params = [])
    {
        // TODO: Implement run() method.
    }

    public function getPretty()
    {
        return null;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->parseInterface($filter['interface']);
    }


}