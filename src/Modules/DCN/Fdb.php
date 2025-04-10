<?php

namespace SwitcherCore\Modules\DCN;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Fdb extends FdbDot1Bridge
{
    use InterfacesTrait;

}
