<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class VlanPortControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        //config vlan vlanid 430 delete 5
        //config vlan vlanid 430 add untagged 5
        switch ($params['action']) {
            case 'add':
                if(!$params['id'] || !$params['type']) {
                    throw new \InvalidArgumentException("For add vlan on port id and type parameters is required");
                }
                return "config vlan vlanid {$params['id']} add {$params['type']} {$params['port']}";
                break;
            case 'delete':
                if($params['id']) {
                    return "config vlan vlanid {$params['id']} delete {$params['port']}";
                }
                throw new \InvalidArgumentException("For action delete vlan from port parameter vlanid is required");
                break;
            default:
                throw new \InvalidArgumentException("Unknown action");
        }
    }
}