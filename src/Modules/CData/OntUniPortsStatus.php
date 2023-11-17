<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * Class OntUniInformation
 * @package SwitcherCore\Modules\CData
 */
class OntUniPortsStatus extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }
    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     */
    private function process($response) {
        $return = [];
        foreach ($response as $raw) {
            $oid = $this->oids->findOidById($raw->getOid());
            $pool = WrappedResponse::init($raw, $oid->getValues());
            $name = substr($oid->getName(), 8);
            switch ($name) {
                case 'opStatus':
                    $name = 'status'; break;
            }
            $key = Helper::fromCamelCase($name);
            if($pool->error()) continue;
            foreach ($pool->fetchAll() as $resp) {
                $ifaceId = Helper::getIndexByOid($resp->getOid(), 2);
                $interface = $this->parseInterface($ifaceId);
                $uni = Helper::getIndexByOid($resp->getOid());
                if($uni) {
                    $interface['uni'] = $uni;
                }
                if(!$uni) {
                    continue;
                }
                $val = $resp->getValue();
                switch ($key) {
                    case 'status': $val = $resp->getParsedValue(); break;
                    case 'admin_status':
                    case 'vlan_mode': $val = $resp->getParsedValue(); break;
                }
                $return[$interface['id']]['unis'][$uni][$key] = $val;
                $return[$interface['id']]['unis'][$uni]['num'] = (int)$uni;
                $return[$interface['id']]['interface'] = $interface;
            }
        }
        foreach ($return as $interfaceID=>$ifaceData) {
            foreach ($ifaceData['unis'] as $uniNum=>$uni) {
                if(isset($uni['admin_status']) && $uni['admin_status'] === 'Disabled') {
                    $return[$interfaceID]['unis'][$uniNum]['status'] = 'Disabled';
                }
            }
        }
        return array_values(array_map(function ($e){
            $e['unis'] = array_values($e['unis']);
            return $e;
        },$return));
    }


    function getPretty()
    {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = [];
        $oidsLoc = $this->oids->getOidsByRegex('^ont\.uni\..*');
        $suffix = '';
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $suffix = '.'.$interface['id'];
        }
        foreach ($oidsLoc as $oid) {
            $oids[] = Oid::init($oid->getOid() . $suffix);
        }
        $this->response = $this->process($this->snmp->walk($oids));

        return $this;
    }
}

