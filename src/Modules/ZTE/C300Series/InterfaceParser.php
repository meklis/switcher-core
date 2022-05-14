<?php


namespace SwitcherCore\Modules\ZTE\C300Series;




class InterfaceParser extends C300ModuleAbstract
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