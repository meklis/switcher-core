<?php

namespace SwitcherCore\Modules\MikrotikCRS;

use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class VlansDot1q extends \SwitcherCore\Modules\General\Switches\VlansDot1q
{
    use InterfacesTrait;

}
