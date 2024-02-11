<?php

namespace SwitcherCore\Modules\HuaweiQuidwayS93;

class System extends \SwitcherCore\Modules\General\Switches\System
{
    use InterfacesTrait;
    function getPrettyFiltered($filter = [])
    {
        $pretty =  parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
        $pretty['descr'] = "HUAWEI " . trim(substr($pretty['descr'], 0,strpos($pretty['descr'], "\r\n")));
        return $pretty;
    }
}