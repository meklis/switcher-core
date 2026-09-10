<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SwitcherCore\Modules\AbstractModule;

class OntBlacklist extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var string
     */
    protected $response = null;

    protected $filter = [];

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $this->filter = $filter;
        $this->response = $this->console->exec("show ont blacklist");
        return $this;
    }

    function getRaw()
    {
        return $this->response;
    }

    /**
     * Collect serial=>interface pairs from ONUs currently registered on the OLT,
     * so blacklist entries (which are keyed by serial, not by interface) can be
     * matched back to a port. Unregistered/autofind ONUs are not checked here -
     * a blacklisted serial can't reach the autofind table in the first place.
     */
    protected function getKnownInterfacesBySerial()
    {
        $known = [];
        try {
            foreach ($this->getModule('pon_onts_serial')->run(['interface' => null])->getPretty() as $ont) {
                if (isset($ont['serial'], $ont['interface'])) {
                    $known[] = ['serial' => strtoupper($ont['serial']), 'interface' => $ont['interface']];
                }
            }
        } catch (\Exception $e) {
        }
        return $known;
    }

    function getPretty()
    {
        $enabled = false;
        if (preg_match('/Blacklist function switch\s*:\s*(\w+)/i', $this->response, $switchMatch)) {
            $enabled = strtolower($switchMatch[1]) === 'enable';
        }

        $entries = [];
        foreach (explode("\n", $this->response) as $line) {
            if (preg_match('/^\s*([0-9]+)\s+([0-9A-Za-z]+)\/([0-9]{1,2})\s+([0-9]+)\s*$/', $line, $m)) {
                $serial = strtoupper($m[2]);
                $entries[] = [
                    'ident' => $serial,
                    'index' => (int)$m[1],
                    'interfaces' => [],
                    '_serial' => $serial,
                    '_mask' => (int)$m[3],
                    '_hit_count' => (int)$m[4],
                ];
            }
        }

        if ($entries) {
            $known = $this->getKnownInterfacesBySerial();
            foreach ($entries as &$entry) {
                $prefix = substr($entry['_serial'], 0, $entry['_mask']);
                foreach ($known as $item) {
                    if (substr($item['serial'], 0, $entry['_mask']) === $prefix) {
                        $entry['interfaces'][] = $item['interface'];
                    }
                }
            }
            unset($entry);
        }

        if (!empty($this->filter['interface'])) {
            $target = $this->parseInterface($this->filter['interface']);
            $entries = array_values(array_filter($entries, function ($entry) use ($target) {
                foreach ($entry['interfaces'] as $iface) {
                    $ifaceId = $iface['type'] === 'ONU' ? $iface['id'] : null;
                    $portId = $iface['type'] === 'ONU' ? $iface['parent'] : $iface['id'];
                    if ($target['type'] === 'ONU') {
                        if ($ifaceId == $target['id']) {
                            return true;
                        }
                    } elseif ($portId == $target['id']) {
                        return true;
                    }
                }
                return false;
            }));
        }

        return [
            'enabled' => $enabled,
            'entries' => $entries,
        ];
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache);
    }
}
