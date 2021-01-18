<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



class CardList extends C300ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->obj->telnet) {
            throw new \Exception("Module required telnet connection");
        }
        $this->response = $this->parseTable(explode("\n", $this->obj->telnet->exec("show card")));
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