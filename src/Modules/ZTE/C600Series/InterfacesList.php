<?php


namespace SwitcherCore\Modules\ZTE\C600Series;




use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class InterfacesList extends ModuleAbstract
{
    public function run($params = [])
    {
        $this->response = [];
        if($params['interface']) {
            $this->response[] = $this->parseInterface($params['interface']);
        } else {
            $this->response =  array_values($this->listInterfacesByXidNames()['id']);
        }
        return  $this;
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