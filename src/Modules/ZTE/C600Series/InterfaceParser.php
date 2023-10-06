<?php


namespace SwitcherCore\Modules\ZTE\C600Series;




use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class InterfaceParser extends ModuleAbstract
{
    public function run($params = [])
    {
        $this->response = $this->parseInterface($params['interface']);
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