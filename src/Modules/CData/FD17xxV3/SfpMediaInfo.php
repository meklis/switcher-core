<?php

namespace SwitcherCore\Modules\CData\FD17xxV3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends CDataAbstractModuleFD17xxV3 {
    public function run($params = []) {
        return $this;
    }

    public function getPretty() {
        return [];
    }

    //public function getPrettyFiltered($filter = [],  $fromCache = false) {
    //}

}