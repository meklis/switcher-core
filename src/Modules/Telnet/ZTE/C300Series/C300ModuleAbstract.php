<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;


use SwitcherCore\Modules\AbstractModule;

abstract class C300ModuleAbstract extends AbstractModule
{
    public function parsePortByName($name) {
        if(preg_match('/^(gpon|epon)-onu_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})/', $name, $matches)) {
            return [
                'type' => $matches[1],
                'shelf' => $matches[2],
                'slot' => $matches[3],
                'port' => $matches[4],
            ];
        }
        throw new \InvalidArgumentException("Error parse port with name '$name'");
    }
}