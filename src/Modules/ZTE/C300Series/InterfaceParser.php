<?php


namespace SwitcherCore\Modules\ZTE\C300Series;




use SwitcherCore\Modules\ZTE\ModuleAbstract;

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