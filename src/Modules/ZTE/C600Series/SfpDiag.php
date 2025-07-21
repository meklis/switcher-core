<?php 

namespace SwitcherCore\Modules\ZTE\C600Series;

use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class SfpDiag extends ModuleAbstract {
    public function run($params = []) {
        $media_failed = false;
        $optical_failed = false;
        try {
            $media = $this->getModule('sfp_media')->run($params)->getPretty();
        } catch (\Exception $e) {
            $media_failed = true;
        }
        try {
            $optical = $this->getModule('sfp_optical')->run($params)->getPretty();
        } catch (\Exception $e) {
            $optical_failed = true;
        }
        if($media_failed && $optical_failed) throw new \Exception("Nothing found by requested interface");
        
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
            if(!isset($v['optical']) && !isset($v['media'])) unset($RESP[$k]);
        }
        $this->response = array_values($RESP);
        return $this;
    }

    public function getPretty() {
       return $this->response;
    }

    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }
}