<?php


namespace SwitcherCore\Switcher\Objects;


use SnmpWrapper\MultiWalkerInterface as MultiWalker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;


/**
 *
 * Class ObjectSource
 *
 * //Object names injects over Core
 * @property MultiWalker $walker
 * @property TelnetLazyConnect $telnet
 * @property RouterOsLazyConnect $routerOsApi
 * @property OidCollector $oidCollector
 * @property ModelCollector $modelCollector
 * @property ModuleCollector $moduleCollector
 * @property Model $model
 *
 */
class InputsStore extends ObjectStore
{

}