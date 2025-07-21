<?php 

namespace SwitcherCore\Modules\General;

use SwitcherCore\Modules\AbstractModule;

class SfpDiag extends AbstractModule {
    public function run($params = []) {
        $errors = [];
        $media = [];
        $optical = [];
        try {
            if(in_array("sfp_media", $this->model->getModulesList())) {
                $media = $this->getModule('sfp_media')->run($params)->getPretty();
            }
        } catch (\Exception $e) {
            $errors["sfp_media"] = $e->getMessage();
        }
        try {
            if(in_array("sfp_optical", $this->model->getModulesList())) {
                $optical = $this->getModule('sfp_optical')->run($params)->getPretty();
            }
        } catch (\Exception $e) {
            $errors["sfp_optical"] = $e->getMessage();
        }
        if($errors) throw new \Exception("Error get data: " . json_encode($errors));
        
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