<?php

namespace SwitcherCore\Modules\General\Switches;

use SwitcherCore\Modules\AbstractModule;

abstract class AbstractInterfaces extends AbstractModule
{
    abstract function parseInterface($iface);
    abstract function getInterfacesIds();
}