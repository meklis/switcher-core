<?php

namespace SwitcherCore\Modules\HuaweiS93;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class Descriptions extends \SwitcherCore\Modules\General\Switches\Descriptions
{
    use InterfacesTrait;

    function getPrettyFiltered($filter = [])
    {
      $pretty = parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
      foreach ($pretty as $k=>$v) {
          if(strpos($v['description'], 'HUAWEI,') === 0) {
              $pretty[$k]['description'] = '';
          }
      }
      return $pretty;
    }
}
