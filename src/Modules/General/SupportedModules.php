<?php


namespace SwitcherCore\Modules\General;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\Helper;

class SupportedModules extends AbstractModule
{
    function getPretty()
    {
        return $this->model->getModulesList();
    }


    function getPrettyFiltered($filter = [])
    {
        return $this->model->getModulesList();
    }

    public function run($filter = [])
    {
        return $this;
    }

}
