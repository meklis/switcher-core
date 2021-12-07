<?php


namespace SwitcherCore\Modules\CData;


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
        if($filter['interface']) {
            $interfaceIds = $this->getOntIdsByInterface($filter['interface']);
            $this->response = array_values(array_filter($this->response, function ($el) use ($interfaceIds) {
                return in_array($el['interface']['id'], $interfaceIds) ;
            }));
        }
        if($filter['vlan_id']) {
            $this->response = array_values(array_filter($this->response, function ($el) use ($filter) {
                return (int)$el['vlan_id'] === (int)$filter['vlan_id'] ;
            }));
        }
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }

    private function processWithoutInterface() {

        $oidLoc[] = $this->oids->getOidByName('pon.fdbWithInterface');
        $oidLoc[] = $this->oids->getOidByName('pon.fdbWithUni');
        $oids = [];
        foreach ($oidLoc as $o) {
            $oids[] = Oid::init($o->getOid());
        }
        $response = $this->formatResponse($this->snmp->walk($oids));

        $return = [];
        /**
         * @var $fdb WrappedResponse
         */
        $fdb = $this->getResponseByName('pon.fdbWithInterface', $response);
        if($fdb->error()) {
            throw new \SNMPException($fdb->error());
        }
        foreach ($this->getResponseByName('pon.fdbWithInterface', $response)->fetchAll() as $r) {
            $interface = $this->parseInterface($r->getValue());
            $vlanId = Helper::getIndexByOid($r->getOid());
            $mac = Helper::oid2macArray([
                Helper::getIndexByOid($r->getOid(), 6),
                Helper::getIndexByOid($r->getOid(), 5),
                Helper::getIndexByOid($r->getOid(), 4),
                Helper::getIndexByOid($r->getOid(), 3),
                Helper::getIndexByOid($r->getOid(), 2),
                Helper::getIndexByOid($r->getOid(), 1),
            ]);
            $return["{$r->getValue()}-{$mac}"] = [
                'interface' => $interface,
                'mac_address' => $mac,
                'vlan_id' => (int)$vlanId,
            ];
        }

        foreach ($this->getResponseByName('pon.fdbWithUni', $response)->fetchAll() as $r) {
            $interface = $this->parseInterface(Helper::getIndexByOid($r->getOid(), 3));
            $interface['uni'] = Helper::getIndexByOid($r->getOid(), 1);
            if(isset($return["{$interface['id']}-{$r->getHexValue()}"])) {
                $return["{$interface['id']}-{$r->getHexValue()}"]['interface'] = $interface;
            }
        }
        return array_values($return);
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($interface) {

        $RETURN = [];
        $oids = [];
        $oid = $this->oids->getOidByName('pon.fdbWithUni');
        $oids[] = Oid::init("{$oid->getOid()}.{$interface['id']}");
        $macDecs = [];
        foreach ($this->snmp->walk($oids) as $resp) {
            $wr = WrappedResponse::init($resp);
            if($wr->error()) continue;
            foreach ($wr->fetchAll() as $r) {
                $interface = $this->parseInterface(Helper::getIndexByOid($r->getOid(), 3));
                $interface['uni'] = Helper::getIndexByOid($r->getOid(), 1);
                $RETURN["{$interface['id']}-{$r->getHexValue()}"] = [
                    'interface' => $interface,
                    'mac_address' => $r->getHexValue(),
                ];
                $macDecs[] = Helper::mac2oid($r->getHexValue());
            }
        }
        $oids = [];
        $parent = $this->oids->getOidByName('pon.fdbWithInterface');
        foreach ($macDecs as $mac) {
            $oids[] = Oid::init("{$parent->getOid()}.{$mac}");
        }
        $response = $this->snmp->walk($oids);
        foreach ($response as $resp) {
            $wr = WrappedResponse::init($resp);
            if($wr->error()) continue;
            foreach ($wr->fetchAll() as $r) {
                $interface = $this->parseInterface($r->getValue());
                $vlanId = Helper::getIndexByOid($r->getOid());
                $mac = Helper::oid2macArray([
                    Helper::getIndexByOid($r->getOid(), 6),
                    Helper::getIndexByOid($r->getOid(), 5),
                    Helper::getIndexByOid($r->getOid(), 4),
                    Helper::getIndexByOid($r->getOid(), 3),
                    Helper::getIndexByOid($r->getOid(), 2),
                    Helper::getIndexByOid($r->getOid(), 1),
                ]);
                $RETURN["{$interface['id']}-{$mac}"]['vlan_id'] = $vlanId;
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
        /**
        - {name: pon.fdbWithInterface, oid: .1.3.6.1.4.1.34592.1.3.100.12.2.1.1.3}
        - {name: pon.fdbWithUni, oid: 1.3.6.1.4.1.34592.1.3.100.13.1.1.5}
         */

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

