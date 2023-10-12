<?php


namespace SwitcherCore\Modules\ZTE\C600Series;



use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;

class UnregisteredOntList extends ModuleAbstract
{
    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        $result = [];
        switch ($params['type']) {
            case '':
            case 'all':
                $result = array_merge($result, $this->getUnregisteredGPON());
 //               $result = array_merge($result, $this->getUnregisteredEPON());
                break;
            case 'gpon':
                $result = array_merge($result, $this->getUnregisteredGPON());
                break;
//            case 'epon':
//                $result = array_merge($result, $this->getUnregisteredEPON());
        }
        $this->response  = $result;
        return $this;
    }


    private function getUnregisteredGPON()
    {
        $oids = [
           Oid::init($this->oids->getOidByName('gpon.uncfg.serial')->getOid()),
           Oid::init($this->oids->getOidByName('gpon.uncfg.type')->getOid()),
           Oid::init($this->oids->getOidByName('gpon.uncfg.fwVersion')->getOid()),
        ];
        $response = $this->formatResponse($this->snmp->walk($oids));
        if($this->getResponseByName('gpon.uncfg.serial', $response)->error() && strpos($this->getResponseByName('gpon.uncfg.serial', $response)->error(), "No Such Instance") !== false) {
            return  [];
        }

        $data = [];
        foreach ($this->getResponseByName('gpon.uncfg.serial', $response)->fetchAll() as $d) {
            $ifaceId = Helper::getIndexByOid($d->getOid(), 1);
            $key = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
            $onuId = Helper::getIndexByOid($d->getOid());
            $iface  = $this->parseInterface($ifaceId);
            $interface = $this->parseInterface("gpon-onu_{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}:{$onuId}");
            $blocks = explode(":", $d->getHexValue());
            $data["{$key}"] = [
                'interface' => $interface,
                'serial' => $this->convertHexToString("{$blocks[0]}:{$blocks[1]}:{$blocks[2]}:{$blocks[3]}") .
                    $blocks[4] . $blocks[5] . $blocks[6] . $blocks[7]
                ,
            ];
        }
        foreach ($this->getResponseByName('gpon.uncfg.type', $response)->fetchAll() as $d) {
            $key = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
            $data["{$key}"]['type'] = $d->getParsedValue();
        }
        foreach ($this->getResponseByName('gpon.uncfg.fwVersion', $response)->fetchAll() as $d) {
            $key = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
            $data["{$key}"]['fw_version'] = $d->getParsedValue();
        }
        return  array_values($data);
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