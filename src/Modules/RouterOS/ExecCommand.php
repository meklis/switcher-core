<?php


namespace SwitcherCore\Modules\RouterOS;

use Exception;
use RouterosAPI;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\RouterOsLazyConnect;


class ExecCommand extends AbstractModule
{
    protected $api;
    protected $status = false;
    function getPretty()
    {
        return $this->status;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->status;
    }

    function __construct(RouterosAPI $api)
    {
        $this->api = $api;
    }

    protected function execComm($comm, $params =[]) {
        $resp = $this->api->comm($comm, $params);
        if(!$resp) {
            return [];
        } elseif (isset($resp['!trap'][0]['message'])) {
            throw  new Exception("RouterOS api returned error - ".$resp['!trap'][0]['message']);
        }
        return $resp;
    }
    public function run($params = [])
    {
        throw new Exception("Method run must be rewrited");
    }

}