<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GponOntFdbTable extends CDataAbstractModule
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
        if($filter['interface']) {
            $interfaceIds = $this->getOntIdsByInterface($filter['interface']);
            $this->response = array_values(array_filter($this->response, function ($el) use ($interfaceIds) {
                return in_array($el['interface']['id'], $interfaceIds) ;
            }));
        }
        if($filter['vlan_id']) {
            $this->response = array_values(array_filter($this->response, function ($el) use ($filter) {
                return $el['vlan_id'] === $filter['vlan_id'] ;
            }));
        }
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }


    private function processWithoutInterface() {
        $response = $this->formatResponse(
            $this->snmp->walk(
                [Oid::init($this->oids->getOidByName('pon.fdbWithUni')->getOid())]
            )
        );
        return $this->parseWalk($response);
    }

    protected function parseWalk($response) {
        $DATA = [];
        foreach ($this->getResponseByName('pon.fdbWithUni', $response)->fetchAll() as $r) {
            $iface = $this->parseInterface(Helper::getIndexByOid($r->getOid(),3));
            $interface = $this->parseInterface($iface['id'] + Helper::getIndexByOid($r->getOid(), 2));
            $interface['uni'] = Helper::getIndexByOid($r->getOid(), 1);
            $DATA[] = [
                'mac_address' =>  $r->getHexValue(),
                'interface' => $interface,
                'vlan_id' => (int)$this->getVlanByMacInterfaceId($r->getHexValue(), $interface['id']),
            ];
        }
        return array_values($DATA);
    }

    protected function getVlanByMacInterfaceId($mac, $interfaceId) {
        $response = $this->snmp->walk(
                [Oid::init($this->oids->getOidByName('dot1q.FdbPort')->getOid() . "." . Helper::mac2oid($mac))]
            );
        foreach ($response as $resp) {
           if($resp->error) continue;
           foreach ($resp->getResponse() as $sresp) {
               if((int)$sresp->getValue() === $interfaceId) {
                   return Helper::getIndexByOid($sresp->getOid());
               }
           }
        }
        return null;
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($interface) {
        $response = $this->formatResponse(
            $this->snmp->walk(
                [Oid::init($this->oids->getOidByName('pon.fdbWithUni')->getOid() . ".{$interface['xid']}.{$interface['_onu']}")]
            )
        );
        return $this->parseWalk($response);
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
                $this->response = $this->processWithInterface($iface);
            }
            return $this;
        }
        $this->response = $this->processWithoutInterface();
        return $this;
    }
}

