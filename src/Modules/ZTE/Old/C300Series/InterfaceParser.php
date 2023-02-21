<?php


namespace SwitcherCore\Modules\ZTE\Old\C300Series;




use SwitcherCore\Modules\ZTE\Old\ModuleAbstract;

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