<?php

namespace SwitcherCore\Modules\Dlink\Acl\Ports;

use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;

/**
 * ACL rules grouped by interface (v4 and v6 together), built on top of the
 * acl_v4_info/acl_v6_info modules rather than its own SNMP walk. One entry
 * per physical interface on the device, even if it carries no ACL rules at
 * all - same "by port" convention as \SwitcherCore\Modules\Dlink\Vlan\VlanByPorts.
 */
class DefaultParser extends SwitchesPortAbstractModule
{
    protected $data;

    function getPretty() {
        return $this->data;
    }

    function getPrettyFiltered($filter = []) {
        if (!empty($filter['interface'])) {
            $interface = $this->parseInterface($filter['interface']);
            return array_values(array_filter($this->data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            }));
        }
        return $this->data;
    }

    /**
     * @param array $filter
     * @return $this
     * @throws \Exception
     */
    public function run($filter = []) {
        $v4 = $this->getModule('acl_v4_info')->run()->getPretty();
        $v6 = $this->getModule('acl_v6_info')->run()->getPretty();

        $grouped = [];
        foreach ($this->getIndexes() as $id => $interface) {
            $grouped[$id] = [
                'interface' => $interface,
                'v4' => [],
                'v6' => [],
            ];
        }
        foreach ($v4 as $e) {
            $id = $e['interface']['id'];
            if (!isset($grouped[$id])) continue;
            $grouped[$id]['v4'][] = [
                'profile_id' => $e['profile_id'],
                'access_id' => $e['access_id'],
                'ip' => $e['ip'],
            ];
        }
        foreach ($v6 as $e) {
            $id = $e['interface']['id'];
            if (!isset($grouped[$id])) continue;
            $grouped[$id]['v6'][] = [
                'profile_id' => $e['profile_id'],
                'access_id' => $e['access_id'],
                'action' => $e['action'],
            ];
        }
        $this->data = array_values($grouped);
        return $this;
    }
}
