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
class OntInterfaceCounters extends CDataAbstractModule
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
                case 'opStatus': $name = 'status'; break;
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
                if(is_numeric($val)) {
                    $val = (int) $val;
                }
                if($val == 9223372036854775807) {
                   continue;
                }
                switch ($key) {
                    case 'status': $val = $resp->getParsedValue(); break;
                    case 'admin_status':
                    case 'vlan_mode': $val = $resp->getParsedValue(); break;
                    case 'stat_in_octets':
                    case 'stat_out_octets': $val = $resp->getValue(); break;
                }
                $return["{$ifaceId}{$uni}"][$key] = $val;
                $return["{$ifaceId}{$uni}"]['interface'] = $interface;
            }
        }
        return array_values($return);
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

