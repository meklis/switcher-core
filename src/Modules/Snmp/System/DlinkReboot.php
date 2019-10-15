<?php


namespace SwitcherCore\Modules\Snmp\System;


use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Switcher\Objects\WrappedResponse;
use \SwitcherCore\Modules\AbstractModule;
use \SwitcherCore\Modules\Helper;

class DlinkReboot extends AbstractModule
{
    protected function formate() {
        return true;
    }
    function getPretty()
    {
        return true ;
    }

    function getPrettyFiltered($filter = [])
    {
        return true;
    }

    public function walk($filter = [])
    {
        $method = $this->oidsCollector->getOidByName('dlink.DevCtrlSystemReboot')->getValueIdByName('reboot');
        if(isset($filter['reboot_method'])) {
            if($filter['reboot_method'] == 'reboot') {
                $method = $this->oidsCollector->getOidByName('dlink.DevCtrlSystemReboot')->getValueIdByName('reboot');
            }
            if($filter['reboot_method'] == 'load_defaults') {
                $method = $this->oidsCollector->getOidByName('dlink.DevCtrlSystemReboot')->getValueIdByName('reboot-and-load-factory-default-config');
            }
            if($filter['reboot_method'] == 'save_config') {
                $method = $this->oidsCollector->getOidByName('dlink.DevCtrlSystemReboot')->getValueIdByName('save-config-and-reboot');
            }
        }
        $this->response = $this->formatResponse($this->walker->set(
            $this->oidsCollector->getOidByName('dlink.DevCtrlSystemReboot')->getOid() ,
            PoollerRequest::TypeIntegerValue,
            $method
        )
        );
        return $this;
    }
}

