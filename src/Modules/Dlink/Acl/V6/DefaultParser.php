<?php

namespace SwitcherCore\Modules\Dlink\Acl\V6;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * D-Link IPv6 ACL profiles (swAuthAclMIB / dlink-common-mgmt.9, ethernet-type
 * based rules - at this ISP used to permit/deny IPv6 traffic per port).
 * Indexed by [profileId, accessId], same shape as the v4 module, but unlike
 * v4 the permit/deny action IS an SNMP-readable object here
 * (Acl6ProfileAction). Its raw integer encoding is inverted between the
 * "managed" tier and the DGS-1100/1210/2000 ME-line - handled declaratively
 * via the `values:` map on the acl.v6.action oid in each model's oids file,
 * so this parser only ever sees the already-resolved 'permit'/'deny' string.
 */
class DefaultParser extends SwitchesPortAbstractModule
{
    protected function formate() {
        $actions = [];
        if (!$this->response['acl.v6.action']->error()) {
            foreach ($this->response['acl.v6.action']->fetchAll() as $r) {
                $profileId = Helper::getIndexByOid($r->getOid(), 1);
                $accessId = Helper::getIndexByOid($r->getOid());
                $actions["{$profileId}:{$accessId}"] = $r->getParsedValue();
            }
        }

        $indexes = $this->getIndexes();
        $entries = [];
        if (!$this->response['acl.v6.ports']->error()) {
            foreach ($this->response['acl.v6.ports']->fetchAll() as $r) {
                $profileId = (int) Helper::getIndexByOid($r->getOid(), 1);
                $accessId = (int) Helper::getIndexByOid($r->getOid());
                $dex = Helper::hexToBinStr($r->getHexValue());
                for ($port = 1; $port <= strlen($dex); $port++) {
                    if ($dex[$port - 1] != '1' || !isset($indexes[$port])) continue;
                    $entries[] = [
                        'interface' => $indexes[$port],
                        'profile_id' => $profileId,
                        'access_id' => $accessId,
                        'action' => isset($actions["{$profileId}:{$accessId}"]) ? $actions["{$profileId}:{$accessId}"] : null,
                    ];
                }
            }
        }
        return $entries;
    }

    function getPretty() {
        return $this->formate();
    }

    function getPrettyFiltered($filter = []) {
        $entries = $this->formate();
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            $entries = array_values(array_filter($entries, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            }));
        }
        return $entries;
    }

    /**
     * @param array $filter
     * @return $this
     * @throws \Exception
     */
    public function run($filter = []) {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('acl.v6.action')->getOid()),
            Oid::init($this->oids->getOidByName('acl.v6.ports')->getOid()),
        ]));
        return $this;
    }
}
