<?php


namespace SwitcherCore\Modules\Raisecom;


use \Exception;
use \SNMPException;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\Raisecom\InterfacesTrait;

class SnoopingInfo extends AbstractModule {

    use InterfacesTrait;

    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw() {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false) {
        $data = $this->getPretty();
        if(count($data) === 0) return [];
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac_address']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac_address']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        return array_values($data);
    }

    function getPretty() {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = []) {

        $oids = [];
        $oids[] = $this->oids->getOidByName('dhcpSnooping.bindMac')->getOid();
        $oids[] = $this->oids->getOidByName('dhcpSnooping.bindLease')->getOid();
        $oids[] = $this->oids->getOidByName('dhcpSnooping.bindVlan')->getOid();
        $oids[] = $this->oids->getOidByName('dhcpSnooping.bindPort')->getOid();
        
        if(isset($filter['ip'])) {
            foreach($oids as $i => $oid) {
                $oids[$i] .= ".4.{$filter['ip']}";
            }
        }
        foreach($oids as $i => $oid) {
            $oids[$i] = Oid::init($oid);
        }
        if(isset($filter['ip'])) {
            $res = $this->formatResponse($this->snmp->get($oids));
        } else {
            $res = $this->formatResponse($this->snmp->walk($oids));
        }
        $resp = [];
        try {
            foreach($res['dhcpSnooping.bindMac']->fetchAll() as $r) {
                $ip = Helper::oid2IP($r->getOid());
                $resp[$ip]['mac_address'] = Helper::formatMac($r->getHexValue());
            }
        } catch(SNMPException $e) {
            throw new Exception("Specified IP-address hasn't been found");
        }
        foreach($res['dhcpSnooping.bindLease']->fetchAll() as $r) {
            $ip = Helper::oid2IP($r->getOid());
            $resp[$ip]['remaining'] = $r->getParsedValue();
        }
        foreach($res['dhcpSnooping.bindVlan']->fetchAll() as $r) {
            $ip = Helper::oid2IP($r->getOid());
            $resp[$ip]['vlan_id'] = $r->getParsedValue();
        }
        foreach($res['dhcpSnooping.bindPort']->fetchAll() as $r) {
            $ip = Helper::oid2IP($r->getOid());
            $resp[$ip]['interface'] = $this->parseInterface($r->getParsedValue());
        }
        $response = [];
        foreach($resp as $ip => $val) {
            $val['ip'] = $ip; 
            $response[] = $val;
        }
        $this->response = $response;
        return $this;
    }
}

