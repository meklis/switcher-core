<?php

namespace SwitcherCore\Modules\UbntSwitch;

class ParseInterface extends \SwitcherCore\Modules\General\Switches\ParseInterface
{
    use InterfacesTrait;
    public function getPrettyFiltered($filter = [])
    {       $filter['interface'] = 7;

        return parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
    }
}
