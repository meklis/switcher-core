<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;



use Exception;

class CardList extends C300ModuleAbstract
{
    public function run($params = [])
    {
        $cache = $this->getCache('card_list');
        if($cache) {
            $this->response = $cache;
            return  $this;
        }
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $this->response = $this->parseTable(explode("\n", $this->telnet->exec("show card")));
        $this->setCache('card_list', $this->response, 3600);
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