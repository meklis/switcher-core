<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class VlanByPorts extends \SwitcherCore\Modules\General\Switches\VlanByPorts
{
    use InterfacesTrait;

}
