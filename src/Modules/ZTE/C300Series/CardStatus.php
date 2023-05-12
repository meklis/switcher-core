<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use Monolog\Logger;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class CardStatus extends ModuleAbstract
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
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.OperStatus')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.AdminStatus')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.CpuLoad')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.temperature')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.MemUsage')->getOid()),
        ]));
        $RESP = [];
        //Validate not errors
        foreach ($response as $k=>$data) {
            if($data->error()) {
                throw new \Exception("Error call {$k} with message: {$data->error()}");
            }
        }
        foreach ($response['zx.slot.OperStatus']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());

            $RESP["{$rack}/{$shelf}/{$slot}"] = [
                'rack' => $rack,
                'shelf' => $shelf,
                'slot' => $slot,
                'oper_status' => $type->getParsedValue(),
                'id' => (int)"10{$rack}{$shelf}{$slot}",
            ];
        }
        foreach ($response['zx.slot.AdminStatus']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['admin_status'] = $type->getParsedValue();
        }
        foreach ($response['zx.slot.CpuLoad']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['cpu_load'] = $type->getParsedValue();
        }
        foreach ($response['zx.slot.temperature']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['temperature'] = $type->getParsedValue() < -900 ? null : (int)$type->getParsedValue();
        }
        foreach ($response['zx.slot.MemUsage']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['memory_usage'] = $type->getParsedValue();
        }

        $RESP = array_filter($RESP, function ($c) {
           return isset($c['oper_status'])  && isset($c['admin_status']);
        });

        $this->response = array_values($RESP);
        $this->setCache("CARD_STATUS", $this->response, 60, true);
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