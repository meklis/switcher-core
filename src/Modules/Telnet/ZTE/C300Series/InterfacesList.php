<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;




class InterfacesList extends C300ModuleAbstract
{
    public function run($params = [])
    {
        $cards = $this->getModule('zte_card_list')->run()->getPretty();
        $response  = [];
        foreach ($cards as $card) {
            switch ($card['real_type']) {
                case 'ETGO': $prefix = "epon"; break;
                case 'GTGOG': $prefix = "gpon"; break;
                default: continue;
            }
            for ($i = 1; $i <= $card['port']; $i++) {
                $response[] = [
                    'interface' => $prefix . "-olt_{$card['shelf']}/{$card['slot']}/$i",
                    '_interface' => [
                        'shelf' => (int)$card['shelf'],
                        'slot' =>  (int)$card['slot'],
                        'port' =>  (int)$i,
                        'type' => $prefix
                    ]
                ];
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