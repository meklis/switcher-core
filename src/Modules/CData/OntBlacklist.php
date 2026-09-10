<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SwitcherCore\Modules\AbstractModule;

class OntBlacklist extends CDataAbstractModule
{
    /**
     * @var array port-id => raw console output
     */
    protected $response = [];

    protected $filter = [];

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $this->filter = $filter;

        $this->console->exec("interface epon 0/0");
        $raw = [];
        foreach ($this->getPortsToQuery($filter) as $port) {
            $raw[$port] = $this->console->exec("show ont black-list {$port} all");
        }
        $this->console->exec("exit");

        $this->response = $raw;
        return $this;
    }

    function getRaw()
    {
        return $this->response;
    }

    /**
     * EPON blacklist is per PON port ("show ont black-list <port-id> all"),
     * there's no single command to dump every port at once.
     */
    protected function getPortsToQuery($filter)
    {
        if (!empty($filter['interface'])) {
            $iface = $this->parseInterface($filter['interface']);
            return [(int)$iface['_port']];
        }
        $ports = [];
        foreach ($this->getInterfacesIds() as $iface) {
            if ($iface['type'] === 'PON') {
                $ports[] = (int)$iface['_port'];
            }
        }
        return $ports;
    }

    /**
     * Collect mac=>interface pairs from ONUs currently registered on the OLT,
     * so blacklist entries (which are keyed by MAC, not by interface) can be
     * matched back to a port.
     */
    protected function getKnownInterfacesByMac()
    {
        $known = [];
        try {
            foreach ($this->getModule('pon_onts_mac_addr')->run(['interface' => null])->getPretty() as $ont) {
                if (isset($ont['mac_address'], $ont['interface'])) {
                    $mac = strtoupper(str_replace([':', '-'], '', $ont['mac_address']));
                    $known[$mac] = $ont['interface'];
                }
            }
        } catch (\Exception $e) {
        }
        return $known;
    }

    function getPretty()
    {
        $entries = [];
        foreach ($this->response as $port => $output) {
            foreach (explode("\n", $output) as $line) {
                if (preg_match('/^\s*([0-9]+)\s+([0-9]+\/[0-9]+)\s+([0-9]+)\s+([0-9A-Fa-f:]{17})\s*$/', $line, $m)) {
                    $mac = strtoupper($m[4]);
                    $entries[] = [
                        'ident' => $mac,
                        'index' => (int)$m[1],
                        'interfaces' => [],
                        '_mac' => $mac,
                        '_frame_slot' => $m[2],
                        '_port' => (int)$m[3],
                    ];
                }
            }
        }

        if ($entries) {
            $known = $this->getKnownInterfacesByMac();
            foreach ($entries as &$entry) {
                $mac = str_replace(':', '', $entry['_mac']);
                if (isset($known[$mac])) {
                    $entry['interfaces'][] = $known[$mac];
                }
            }
            unset($entry);
        }

        if (!empty($this->filter['interface'])) {
            $target = $this->parseInterface($this->filter['interface']);
            $entries = array_values(array_filter($entries, function ($entry) use ($target) {
                if ($target['type'] === 'ONU') {
                    foreach ($entry['interfaces'] as $iface) {
                        if ($iface['id'] == $target['id']) {
                            return true;
                        }
                    }
                    return false;
                }
                return $entry['_port'] == $target['_port'];
            }));
        }

        return [
            'enabled' => null,
            'entries' => $entries,
        ];
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache);
    }
}
