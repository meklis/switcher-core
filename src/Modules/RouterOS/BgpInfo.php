<?php

namespace SwitcherCore\Modules\RouterOS;

/**
 * BGP info in the same format as SNMP based bgp_info (version, local_as, identifier).
 * RouterOS can have several BGP instances, in this case the instance with the most enabled peers is used.
 */
class BgpInfo extends AbstractBgp
{
    public function run($params = [])
    {
        $peers = $this->loadPeers();
        $enabledPeers = array_filter($peers, function ($p) {
            return $p['admin_status'] === 'enabled';
        });
        $establishedPeers = array_filter($peers, function ($p) {
            return $p['state'] === 'established';
        });

        $localAs = null;
        $identifier = null;

        // RouterOS v6 and v7.20+
        $instances = $this->execOptional('/routing/bgp/instance/print');
        if ($instances) {
            $instance = $this->selectInstance($instances, $establishedPeers, $enabledPeers);
            $localAs = $instance['as'] ?? null;
            $identifier = $instance['router-id'] ?? null;
        } else {
            // RouterOS v7 before 7.20 - AS and router-id are set per connection/template
            $localAs = $this->mostCommon(array_column($establishedPeers ?: ($enabledPeers ?: $peers), '__local_as'));
            $identifier = $this->mostCommon(array_column($establishedPeers ?: ($enabledPeers ?: $peers), '__local_id'));
            if (!$localAs) {
                foreach ($this->execOptional('/routing/bgp/template/print') ?: [] as $template) {
                    if (($template['name'] ?? '') === 'default') {
                        $localAs = $template['as'] ?? null;
                    }
                }
            }
            if (!$identifier) {
                foreach ($this->execOptional('/routing/id/print') ?: [] as $id) {
                    if (($id['name'] ?? '') === 'main') {
                        $identifier = $id['id'] ?? ($id['dynamic-id'] ?? null);
                    }
                }
            }
        }

        $this->response = [
            'version' => '4',
            'local_as' => $localAs,
            'identifier' => $identifier,
        ];
        return $this;
    }

    /**
     * Instance with the most established peers, then with the most enabled peers
     */
    protected function selectInstance(array $instances, array $establishedPeers, array $enabledPeers)
    {
        $established = array_count_values(array_filter(array_column($establishedPeers, '__instance')));
        $enabled = array_count_values(array_filter(array_column($enabledPeers, '__instance')));
        $score = function ($instance) use ($established, $enabled) {
            $name = $instance['name'] ?? '';
            return [$established[$name] ?? 0, $enabled[$name] ?? 0];
        };
        usort($instances, function ($a, $b) use ($score) {
            return $score($b) <=> $score($a);
        });
        return $instances[0];
    }

    protected function mostCommon(array $values)
    {
        $values = array_filter($values);
        if (!$values) {
            return null;
        }
        $counts = array_count_values($values);
        arsort($counts);
        return (string)array_key_first($counts);
    }
}
