<?php

namespace SwitcherCore\Modules\General\Switches;

abstract class ParseInterface extends AbstractInterfaces
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