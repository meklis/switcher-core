<?php

namespace SwitcherCore\Modules\CData\FD16xxV3;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends CDataAbstractModuleFD16xxV3 {
    public function run($params = []) {
        return $this;
    }

    public function getPretty() {
        return [];
    }

    //public function getPrettyFiltered($filter = [],  $fromCache = false) {
    //}

}