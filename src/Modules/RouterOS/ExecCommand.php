<?php


namespace SwitcherCore\Modules\RouterOS;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;


class ExecCommand extends AbstractModule
{

    protected $status = false;
    function getPretty()
    {
        return $this->status;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->status;
    }
    protected function execComm($comm, $params =[]) {
        if(!$this->routerOsAPI) {
            throw new \Exception("Module required routerOsApi connection");
        }
        $resp = $this->routerOsAPI->comm($comm, $params);
        if(!$resp) {
            return [];
        } elseif (isset($resp['!trap'][0]['message'])) {
            throw  new \Exception("RouterOS api returned error - ".$resp['!trap'][0]['message']);
        }
        return $resp;
    }
    public function run($params = [])
    {
        throw new \Exception("Method run must be rewrited");
    }

}