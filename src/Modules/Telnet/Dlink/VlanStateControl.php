<?php


namespace SwitcherCore\Modules\Telnet\Dlink;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class VlanStateControl extends ExecLineCtrl
{
    private function getRevertSpeed($speed) {
      return strtolower(str_replace('-', '_', $speed));
    }
    function getCommandLine($params = [])
    {
        switch ($params['action']) {
            case 'create':
                if(!$params['id'] || !$params['name']) {
                    throw new \InvalidArgumentException("For create vlan arguments id and name is required");
                }
                return "create vlan {$params['name']} tag {$params['id']}";
                break;
            case 'delete':
                if($params['name']) {
                    return "delete vlan {$params['name']}";
                }
                if($params['id']) {
                    return "delete vlan vlanid {$params['id']}";
                }
                throw new \InvalidArgumentException("For action delete vlan arguments id or name is required");
                break;
            default:
                throw new \InvalidArgumentException("Unknown action");
        }
    }
}