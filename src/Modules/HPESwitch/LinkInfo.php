<?php

namespace SwitcherCore\Modules\HPESwitch;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class LinkInfo extends \SwitcherCore\Modules\General\Switches\LinkInfo
{
    use InterfacesTrait;

}
