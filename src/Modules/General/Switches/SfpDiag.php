<?php

namespace SwitcherCore\Modules\General\Switches;

abstract class SfpDiag extends AbstractInterfaces {
    public function run($params = []) {
        $media = $this->getModule('sfp_media')->run($params)->getPretty();
        $optical = $this->getModule('sfp_optical')->run($params)->getPretty();
        $RESP = [];
        foreach ($media as $m) {
            $iface = $m['interface'];
            unset($m['interface']);
            $RESP[$iface['id']]['interface'] = $iface;
            $RESP[$iface['id']]['media'] = $m;
        }
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

    public function getPretty()
    {
       return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
}
