<?php

namespace SwitcherCore\Modules\Dlink;

class InterfacesList extends SwitchesPortAbstractModule
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
        if($filter['interface'])  {
            return [$this->parseInterface($filter['interface'])];
        } else {
            return array_values($this->getIndexes());
        }
    }


}