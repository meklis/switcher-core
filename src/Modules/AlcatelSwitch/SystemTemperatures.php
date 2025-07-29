<?php


namespace SwitcherCore\Modules\AlcatelSwitch;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemTemperatures extends \SwitcherCore\Modules\General\SystemTemperatures
{
    public function run($filter = [])
    {
        $returnedGets = $this->snmp->get([
            Oid::init($this->oids->getOidByName('resources.temperature.board')->getOid())
        ]);
        $this->response = $this->formatResponse($returnedGets);
        return $this;
    }
}

