<?php


namespace SwitcherCore\Modules\HuaweiOLT;



use Exception;
use Monolog\Logger;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;

class CardStatus extends HuaweiOLTAbstractModule
{
    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    public function run($params = [])
    {
        if($this->response) {
             return  $this;
        }
        if($cached = $this->getCache("CARD_STATUS", true)) {
            $this->response = $cached;
            return  $this;
        }
        $response = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.operStatus')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.adminState')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.cpuUtil')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('sys.slot.temp')->getOid()),
        ]));
        $RESP = [];
        //Validate not errors
        foreach ($response as $k=>$data) {
            if($data->error()) {
                throw new \Exception("Error call {$k} with message: {$data->error()}");
            }
        }
        foreach ($response['sys.slot.operStatus']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$shelf}/{$slot}"] = [
                'shelf' => $shelf,
                'slot' => $slot,
                'admin_status' => null,
                'cpu_load' => null,
                'temperature' => null,
                'oper_status' =>  "{$type->getParsedValue()} ({$type->getValue()})",
                '_oper_status' => $type->getValue(),
                'id' => (int)"100{$shelf}{$slot}",
            ];
        }
        foreach ($response['sys.slot.adminState']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$shelf}/{$slot}"]['admin_status'] =  "{$type->getParsedValue()} ({$type->getValue()})";
            $RESP["{$shelf}/{$slot}"]['_admin_status'] = $type->getValue();
        }
        foreach ($response['sys.slot.cpuUtil']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$shelf}/{$slot}"]['cpu_load'] = $type->getParsedValue();
        }
        foreach ($response['sys.slot.temp']->fetchAll() as $type) {
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $temp = $type->getParsedValue();
            if($temp > 10000) {
                $temp = null;
            }
            $RESP["{$shelf}/{$slot}"]['temperature'] = $temp;
        }
        $RESP = array_filter($RESP, function ($c) {
           return isset($c['oper_status'])  && isset($c['admin_status']);
        });

        $this->response = array_values($RESP);
        $this->setCache("CARD_STATUS", $this->response, 10, true);
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