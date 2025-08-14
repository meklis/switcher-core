<?php


namespace SwitcherCore\Modules\General;

use \Exception;
use SwitcherCore\Modules\AbstractModule;
use InvalidArgumentException;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

abstract class FdbDot1BridgeWithConsole extends AbstractModule {
    public function getPrettyFiltered($filter = []) {
        return $this->response;
    }

    public function getPretty() {
        return $this->response;
    }

    public function run($filter = []) {
        Helper::prepareFilter($filter);
        if($filter['vlan_id'] || $filter['mac'] || $filter['interface']) {
           $this->response = $this->getFromConsole($filter);
        } else {
           $this->response = $this->getFromSNMP();
        }
        return $this;
    }

    protected function getFromSNMP() {
        $fdb_port = $this->oids->getOidByName('dot1q.FdbPort')->getOid();
        $fdb_status = $this->oids->getOidByName('dot1q.FdbStatus')->getOid();
        $res = $this->formatResponse($this->snmp->walkBulk([
            Oid::init($fdb_status), Oid::init($fdb_port),
        ]));

        $response = [];
        $statuses = [];
        $ports = [];

        if($res['dot1q.FdbStatus']->error()) {
            throw new Exception("Returned error {$res['dot1q.FdbStatus']->error()} from {$res['dot1q.FdbStatus']->getRaw()->ip}");
        } else {
            while ($d = $res['dot1q.FdbStatus']->fetchOne()) {
                $data = Helper::oid2MacVlan($d->getOid());
                $statuses["{$data['vid']}-{$data['mac']}"] = $d->getParsedValue();
            }
        }
        if($res['dot1q.FdbPort']->error()) {
            throw new Exception("Returned error {$res['dot1q.FdbPort']->error()} from {$res['dot1q.FdbPort']->getRaw()->ip}");
        } else {
            while ($d = $res['dot1q.FdbPort']->fetchOne()) {
                $data = Helper::oid2MacVlan($d->getOid());
                $ports["{$data['vid']}-{$data['mac']}"] = $d->getValue();
            }
        }
        foreach ($statuses as $key=>$status) {
            list($vlanId, $macAddr) = explode("-", $key);
            if(!isset($ports[$key])) {
                continue;
            }
            if(!(int)$ports[$key])  continue;
            try {
                $response[] = [
                    'interface' => $this->parseInterface($ports[$key]),
                    'vlan_id' => (int)$vlanId,
                    'mac_address' => $macAddr,
                    'status' => $status,
                ];
            } catch (\Throwable $e) {}
        }
        return array_values($response);
    }

    protected abstract function getFromConsole(array $filter);
}