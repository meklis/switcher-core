<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use Monolog\Logger;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class CardStatus extends ModuleAbstract
{
    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    public function run($params = [])
    {
        $response = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.OperStatus')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.AdminStatus')->getOid()),
        //    \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.CpuLoad')->getOid()),  // no correct value by snmp on c600
        //    \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.MemUsage')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zxr10.boardTempDescription')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zxr10.boardTempCurTemp')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zxr10.systemCpuUtil1m')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zxr10.systemMemUsed')->getOid()),
        ]));
        $RESP = [];
        //Validate not errors
        // foreach ($response as $k=>$data) {
        //     if($data->error()) {
        //         throw new \Exception("Error call {$k} with message: {$data->error()}");
        //     }
        // }
        if($response['zx.slot.OperStatus']->error()) throw new \Exception("Error call 'zx.slot.OperStatus' with message: {$response['zx.slot.OperStatus']->error()}");
        if($response['zx.slot.AdminStatus']->error()) throw new \Exception("Error call 'zx.slot.AdminStatus' with message: {$response['zx.slot.AdminStatus']->error()}");
        
        foreach ($response['zx.slot.OperStatus']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());

            $RESP["{$rack}/{$shelf}/{$slot}"] = [
                'rack' => $rack,
                'shelf' => $shelf,
                'slot' => $slot,
              //  'oper_status' =>  "{$type->getParsedValue()} ({$type->getValue()})",
                'oper_status' =>   $type->getParsedValue() ,
                '_oper_status' => $type->getValue(),
                'temperature' => null,
                'memory_usage' => null,
                'cpu_load' => null,
                'admin_status' => null,
                'id' => (int)"10{$rack}{$shelf}{$slot}",
            ];
        }
        foreach ($response['zx.slot.AdminStatus']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
           // $RESP["{$rack}/{$shelf}/{$slot}"]['admin_status'] =  "{$type->getParsedValue()} ({$type->getValue()})";
            $RESP["{$rack}/{$shelf}/{$slot}"]['admin_status'] =   $type->getParsedValue() ;
            $RESP["{$rack}/{$shelf}/{$slot}"]['_admin_status'] = $type->getValue();
        }
        // foreach ($response['zx.slot.CpuLoad']->fetchAll() as $type) {                // no correct value by snmp on c600
        //     $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
        //     $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);       
        //     $slot = (int)Helper::getIndexByOid($type->getOid());
        //     $RESP["{$rack}/{$shelf}/{$slot}"]['cpu_load'] = $type->getParsedValue();
        // }                                                                            // no correct value by snmp on c600
//        foreach ($response['zx.slot.temperature']->fetchAll() as $type) {
//            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
//            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
//            $slot = (int)Helper::getIndexByOid($type->getOid());
//            $RESP["{$rack}/{$shelf}/{$slot}"]['temperature'] = $type->getParsedValue() < -900 ? null : (int)$type->getParsedValue();
//        }
//        foreach ($response['zx.slot.MemUsage']->fetchAll() as $type) {
//            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
//            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
//            $slot = (int)Helper::getIndexByOid($type->getOid());
//            $RESP["{$rack}/{$shelf}/{$slot}"]['memory_usage'] = $type->getParsedValue();
//        }

        $temp_descr = [];
        if(!$response['zxr10.boardTempDescription']->error()) {
            foreach ($response['zxr10.boardTempDescription']->fetchAll() as $val) {
                $rack = (int)Helper::getIndexByOid($val->getOid(), 4);
                $shelf = (int)Helper::getIndexByOid($val->getOid(), 3);
                $slot = (int)Helper::getIndexByOid($val->getOid(), 2);
                $component_num = (int)Helper::getIndexByOid($val->getOid());
                if($val->getValue() === 'cpu' || $val->getValue() === 'fpp') {
                    $temp_descr["{$rack}/{$shelf}/{$slot}"] = $component_num;
                }
            }
        }
        if(!$response['zxr10.boardTempCurTemp']->error()) {
            foreach($response['zxr10.boardTempCurTemp']->fetchAll() as $val) {
                $rack = (int)Helper::getIndexByOid($val->getOid(), 4);
                $shelf = (int)Helper::getIndexByOid($val->getOid(), 3);
                $slot = (int)Helper::getIndexByOid($val->getOid(), 2);
                $component_num = (int)Helper::getIndexByOid($val->getOid());
                if(!isset($temp_descr["{$rack}/{$shelf}/{$slot}"])) continue;
                $need_component_num = $temp_descr["{$rack}/{$shelf}/{$slot}"];
                if(isset($RESP["{$rack}/{$shelf}/{$slot}"]) && $component_num === $need_component_num) {
                    $RESP["{$rack}/{$shelf}/{$slot}"]['temperature'] = $val->getValue();
                }
            }
        }
        if(!$response['zxr10.systemCpuUtil1m']->error()) {
            foreach($response['zxr10.systemCpuUtil1m']->fetchAll() as $val) {
                $rack = (int)Helper::getIndexByOid($val->getOid(), 3);
                $shelf = (int)Helper::getIndexByOid($val->getOid(), 2);
                $slot = (int)Helper::getIndexByOid($val->getOid(), 1);
                if(isset($RESP["{$rack}/{$shelf}/{$slot}"])) {
                    $RESP["{$rack}/{$shelf}/{$slot}"]['cpu_load'] = $val->getValue();
                }
            }
        }
        if(!$response['zxr10.systemMemUsed']->error()) {
            foreach($response['zxr10.systemMemUsed']->fetchAll() as $val) {
                $rack = (int)Helper::getIndexByOid($val->getOid(), 3);
                $shelf = (int)Helper::getIndexByOid($val->getOid(), 2);
                $slot = (int)Helper::getIndexByOid($val->getOid(), 1);
                if(isset($RESP["{$rack}/{$shelf}/{$slot}"])) {
                    $RESP["{$rack}/{$shelf}/{$slot}"]['memory_usage'] = $val->getValue();
                }
            }
        }

        $this->response = array_values($RESP);
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