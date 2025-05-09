<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntFDBTable extends CDataAbstractModule
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
        if($filter['mac']) {
            $this->response = array_values(array_filter($this->response, function ($el) use ($filter) {
                return $el['mac_address'] === $filter['mac'] ;
            }));
        }
        if($filter['vlan_id']) {
            $this->response = array_values(array_filter($this->response, function ($el) use ($filter) {
                return (int)$el['vlan_id'] === (int)$filter['vlan_id'] ;
            }));
        }
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function process($interface = null) {

        $RETURN = [];
        $oids = [];
        $oid = $this->oids->getOidByName('pon.fdbWithUni');
        if($interface) {
            $oids[] = Oid::init("{$oid->getOid()}.{$interface['_snmp_id']}");
        } else {
            $oids[] = Oid::init("{$oid->getOid()}");
        }
        foreach ($this->snmp->walk($oids) as $resp) {
            $wr = WrappedResponse::init($resp);
            if($wr->error()) continue;
            foreach ($wr->fetchAll() as $r) {
                $onuNum = Helper::getIndexByOid($r->getOid(), 2);
                $ponNum = Helper::getIndexByOid($r->getOid(), 3);
                $interface = $this->parseInterface(($ponNum * 1000) + $onuNum);
                $interface['uni'] = Helper::getIndexByOid($r->getOid(), 1);
                $RETURN[] = [
                    'interface' => $interface,
                    'mac_address' => $r->getHexValue(),
                    'vlan_id' => 0,
                    'uni' => Helper::getIndexByOid($r->getOid(), 1),
                ];
            }
        }
        return array_values($RETURN);
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
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if($iface['type'] === 'ONU') {
                $this->response = $this->process($iface);
            }
            return $this;
        }
        $this->response = $this->process();
        return $this;
    }
}

