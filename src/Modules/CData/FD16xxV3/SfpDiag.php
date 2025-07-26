<?php 

namespace SwitcherCore\Modules\CData\FD16xxV3;

use SwitcherCore\Modules\CData\FD16xxV3\CDataAbstractModuleFD16xxV3;

class SfpDiag extends CDataAbstractModuleFD16xxV3 {
    public function run($params = []) {
        $optical = $this->getModule('sfp_optical')->run($params)->getPretty();

        $RESP = [];
        foreach ($optical as $m) {
            $iface = $m['interface'];
            unset($m['interface']);
            $RESP[$iface['id']]['interface'] = $iface;
            $RESP[$iface['id']]['optical'] = $m;
        }
        foreach ($RESP as $k => $v) {
            if(!isset($v['optical'])) $RESP[$k]['optical'] = null;
            if(!isset($v['media'])) $RESP[$k]['media'] = null;
        }
        $this->response = array_values($RESP);
        return $this;
    }

    public function getPretty() {
       return $this->response;
    }

    public function getPrettyFiltered($filter = [], $from_cache = false) {
        return $this->response;
    }

}