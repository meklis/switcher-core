<?php


namespace SwitcherCore\Modules\Snmp\ZTE;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class SlotInfo extends AbstractModule
{
    public function run($params = [])
    {
        $oidsObj = $this->obj->oidCollector->getOidsByRegex('^zx.slot.*');
        $oids = [];
        foreach ($oidsObj as $oid) {
            $oids[] = $oid->getOid();
        }
        $this->response = $this->formatResponse($this->obj->walker->walk($oids));
        return $this;
    }

    protected function format() {
        $response = [];

        $data = $this->getResponseByName('zx.slot.ActType');
        foreach ($data->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid()) ;
            $response[$index] = [
                'id' => $index,
                'slot_num' => $index,
                'num_ports' => null,
                'type' => $resp->getParsedValue(),
                'oper_status' => null,
                'admin_status' => null,
                'cpu_load' => null,
                'temperature' => null,
                'memory_usage' => null,
            ];
        }
        foreach($this->getResponseByName('zx.slot.OperStatus')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index])) {
                $response[$index]['oper_status'] = $resp->getParsedValue();
            }
        }
        foreach($this->getResponseByName('zx.slot.AdminStatus')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index])) $response[$index]['admin_status'] = $resp->getParsedValue();
        }
        foreach($this->getResponseByName('zx.slot.CpuLoad')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index])) $response[$index]['cpu_load'] = $resp->getParsedValue();
        }
        foreach($this->getResponseByName('zx.slot.temperature')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index]) && $resp->getParsedValue() != -1000 ) $response[$index]['temperature'] = $resp->getParsedValue();
        }
        foreach($this->getResponseByName('zx.slot.MemUsage')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index])) $response[$index]['memory_usage'] = $resp->getParsedValue();
        }
        foreach($this->getResponseByName('zx.slot.NumPorts')->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if(isset($response[$index])) $response[$index]['num_ports'] = $resp->getParsedValue();
        }

        return $response;
    }
    public function getPretty()
    {
        // TODO: Implement getPretty() method.
    }

    public function getPrettyFiltered($filter = [])
    {
        $formated = $this->format();
        if($filter['slot_num'] && !isset($formated[$filter['slot_num']])) {
            throw new \InvalidArgumentException("Not found any information about slot with num = {$filter['num']}");
        }
        if($filter['slot_num']) {
            return [$formated[$filter['slot_num']]];
        }
        return array_values($formated);
    }

}