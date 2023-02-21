<?php


namespace SwitcherCore\Modules\ZTE\C300Series;




use SwitcherCore\Modules\ZTE\ModuleAbstract;

class PonPortsList extends ModuleAbstract
{
    public function run($params = [])
    {
        $response  = [];
        $cards = $this->getModule('card_list')->run()->getPretty();
        foreach ($cards as $card) {
            if(!$card['technology']) continue;
            for($port = 1; $port <= $card['num_ports']; $port++) {
                $response[] = $this->parseInterface("{$card['technology']}-olt_{$card['shelf']}/{$card['slot']}/{$port}");
            }
        }
        $this->response = $response;
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