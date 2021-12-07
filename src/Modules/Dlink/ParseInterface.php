<?php

namespace SwitcherCore\Modules\Dlink;

class ParseInterface extends SwitchesPortAbstractModule
{
    public function run($params = [])
    {
       return $this;
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