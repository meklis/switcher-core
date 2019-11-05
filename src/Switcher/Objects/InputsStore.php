<?php


namespace SwitcherCore\Switcher\Objects;


use SnmpWrapper\Walker;
use SwitcherCore\Config\ModelCollector;
use SwitcherCore\Config\ModuleCollector;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Modules\AbstractModule;


/**
 *
 * Class ObjectSource
 *
 * //Object names injects over Core
 * @property Walker $walker
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