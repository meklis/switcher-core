<?php


namespace SwitcherCore\Modules\HuaweiOLT;



use Exception;
use Monolog\Logger;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;

class CardList extends HuaweiOLTAbstractModule
{
    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    protected $types = [
        'EP' => 'epon',
        'GP' => 'gpon',
        'XE' => 'xepon',
        'XG' => 'xgpon',
    ];


    public function run($params = [])
    {

//        $cache = $this->getCache('card_list', true);
//        if($cache) {
//            $this->response = $cache;
//            return  $this;
//        } else {
//            $this->logger->notice("Cache by key 'card_list' not found");
//        }

        $response = $this->formatResponse($this->snmp->walkNext([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.typeName')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.sub.countPorts')->getOid()),
        ]));
        $RESP = [];
        //Validate not errors
        foreach ($response as $k=>$data) {
            if($data->error()) {
                throw new \Exception("Error call {$k} with message: {$data->error()}");
            }
        }
        foreach ($response['sys.slot.typeName']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $technology = null;
            $slotType = substr($type->getValue(), -4, 2);
            if(isset($this->types[$slotType])) {
                $technology = $this->types[$slotType];
            }

            $RESP["{$shelf}/{$slot}"] = [
                'shelf' => $shelf,
                'slot' => $slot,
                'cfg_type' => $type->getValue(),
                'num_ports' => null,
                'technology' => $technology,
                'id' => (int)"100{$shelf}{$slot}",
            ];
        }
        foreach ($response['sys.slot.sub.countPorts']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 2);
            $slot = (int)Helper::getIndexByOid($type->getOid(), 1);
            $RESP["{$shelf}/{$slot}"]['num_ports'] = $type->getValue();
        }


        $this->response = array_values($RESP);
        $this->setCache('card_list', $this->response, 300, true);
        return  $this;
    }
    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [], $from = 'cache')
    {
        return $this->response;
    }

}