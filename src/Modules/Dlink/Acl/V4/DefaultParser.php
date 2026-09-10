<?php

namespace SwitcherCore\Modules\Dlink\Acl\V4;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

/**
 * D-Link IPv4 ACL profiles (swAuthAclMIB / dlink-common-mgmt.9, the "managed"
 * tier tree - some ME-line models expose it too via a per-model private OID
 * base, wired per-model in their own oids file).
 *
 * A rule is indexed by [profileId, accessId] and applies to a bitmap of
 * ports; AclProfileIpList carries the source-IP the profile was created
 * with. The permit/deny action itself is NOT retrievable via SNMP - it's set
 * only through the CLI 'add access_id ... permit/deny' and isn't reflected
 * in any of these MIB objects - so this module surfaces the raw
 * profile/access-id/IP/port structure only, same as the vendor MIB does.
 */
class DefaultParser extends SwitchesPortAbstractModule
{
    protected function formate() {
        $ips = [];
        if (!$this->response['acl.v4.ipList']->error()) {
            foreach ($this->response['acl.v4.ipList']->fetchAll() as $r) {
                $profileId = Helper::getIndexByOid($r->getOid(), 1);
                $accessId = Helper::getIndexByOid($r->getOid());
                $ips["{$profileId}:{$accessId}"] = $r->getValue();
            }
        }

        $indexes = $this->getIndexes();
        $entries = [];
        if (!$this->response['acl.v4.ports']->error()) {
            foreach ($this->response['acl.v4.ports']->fetchAll() as $r) {
                $profileId = (int) Helper::getIndexByOid($r->getOid(), 1);
                $accessId = (int) Helper::getIndexByOid($r->getOid());
                $dex = Helper::hexToBinStr($r->getHexValue());
                for ($port = 1; $port <= strlen($dex); $port++) {
                    if ($dex[$port - 1] != '1' || !isset($indexes[$port])) continue;
                    $entries[] = [
                        'interface' => $indexes[$port],
                        'profile_id' => $profileId,
                        'access_id' => $accessId,
                        'ip' => isset($ips["{$profileId}:{$accessId}"]) ? $ips["{$profileId}:{$accessId}"] : null,
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
            Oid::init($this->oids->getOidByName('acl.v4.ipList')->getOid()),
            Oid::init($this->oids->getOidByName('acl.v4.ports')->getOid()),
        ]));
        return $this;
    }
}
