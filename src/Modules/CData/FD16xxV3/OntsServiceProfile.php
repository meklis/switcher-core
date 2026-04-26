<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntsServiceProfile extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    protected $entries = [];

    /**
     * Detail columns of servicePortCfgInfoTable used for the SNMP path
     * (no filter — walks the whole table).
     */
    const SNMP_COLUMNS = [
        'srv_port.svlan',
        'srv_port.pon_port',
        'srv_port.onu_id',
        'srv_port.gem_id',
        'srv_port.user_vlan',
        'srv_port.user_vlan_pri',
        'srv_port.eth_type',
        'srv_port.up_traffic_id',
        'srv_port.down_traffic_id',
        'srv_port.admin_status',
        'srv_port.online_state',
        'srv_port.tag_action',
        'srv_port.inner_vid',
        'srv_port.inner_pri',
        'srv_port.description',
    ];

    function getRaw()
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
        $this->entries = [];

        if (isset($filter['interface']) && $filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $this->entries = $this->runConsole($iface);
        } else {
            $this->entries = $this->runSnmp();
        }

        return $this;
    }

    function getPretty()
    {
        return $this->entries;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->entries;
    }

    private function runConsole($iface)
    {
        if ($iface['type'] !== 'ONU' && $iface['type'] !== 'PON') {
            throw new Exception("Interface type {$iface['type']} not supported, only ONU/PON");
        }

        $shelf = $iface['_shelf'];
        $slot = $iface['_slot'];
        $port = $iface['_port'];
        $cmd = "show service-port gpon {$shelf}/{$slot} port {$port}";
        if (!empty($iface['_onu'])) {
            $cmd .= " ont {$iface['_onu']}";
        }

        $this->logger->debug("OntsServiceProfile console cmd: {$cmd}");
        $output = $this->console->exec($cmd);
        $this->response = ['_console_output' => $output];

        return $this->parseConsoleTable($output);
    }

    private function parseConsoleTable($output)
    {
        $entries = [];
        $lines = preg_split('/\r?\n/', $output);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '' || strpos($line, '---') === 0 || strpos($line, 'Total') !== false) {
                continue;
            }
            if (preg_match('/^\s*INDEX/i', $line) || preg_match('/^\s*ID\s+ID/i', $line)) {
                continue;
            }
            $cells = preg_split('/\s+/', trim($line));
            // Expected: INDEX VLAN PORT ONT GEM FLOW_TYPE FLOW_PARAM TAG_ACTION INNER_VLAN INNER_PRIO RX TX STATUS METHOD CONFIG_STATUS
            if (count($cells) < 15 || !ctype_digit($cells[0])) {
                continue;
            }

            $servicePortId = (int)$cells[0];
            $portId = $cells[2];
            $ponPort = null;
            if (preg_match('#^(\d+)/(\d+)/(\d+)$#', $portId, $m)) {
                $ponPort = (int)$m[3];
            }
            $onuId = is_numeric($cells[3]) ? (int)$cells[3] : null;

            $entry = [
                'service_port_id' => $servicePortId,
                'pon_port' => $ponPort,
                'onu_id' => $onuId,
                'svlan' => $this->normalizeNumeric($cells[1]),
                'user_vlan' => null,
                'user_vlan_pri' => null,
                'gem_id' => $this->normalizeNumeric($cells[4]),
                'eth_type' => null,
                'flow_type' => $this->normalizeString($cells[5]),
                'flow_param' => $this->normalizeString($cells[6]),
                'tag_action' => $this->normalizeString($cells[7]),
                'inner_vid' => $this->normalizeNumeric($cells[8]),
                'inner_pri' => $this->normalizeNumeric($cells[9]),
                'admin_status' => $this->mapAdminStatus($cells[12]),
                'online_state' => $this->mapOnlineState($cells[12]),
                'description' => null,
                'up_traffic_profile' => null,
                'down_traffic_profile' => null,
                'method' => $this->normalizeString($cells[13]),
                'config_status' => $this->normalizeString($cells[14]),
            ];

            try {
                if ($onuId !== null && $ponPort !== null) {
                    $entry['interface'] = $this->buildOntInterface((int)$ponPort, (int)$onuId);
                }
            } catch (\Throwable $e) {
                $entry['interface'] = null;
            }

            $entries[] = $entry;
        }
        return $entries;
    }

    private function normalizeNumeric($cell)
    {
        if ($cell === '-' || $cell === '') {
            return null;
        }
        return is_numeric($cell) ? (int)$cell : null;
    }

    private function normalizeString($cell)
    {
        if ($cell === '-' || $cell === '') {
            return null;
        }
        return $cell;
    }

    private function mapAdminStatus($status)
    {
        $s = strtolower($status);
        if ($s === 'up' || $s === 'down') {
            return 'Enabled';
        }
        return null;
    }

    private function mapOnlineState($status)
    {
        $s = strtolower($status);
        if ($s === 'up') {
            return 'Online';
        }
        if ($s === 'down') {
            return 'Offline';
        }
        return null;
    }

    private function runSnmp()
    {
        $oids = [];
        foreach (self::SNMP_COLUMNS as $name) {
            $oidObj = $this->oids->getOidByName($name);
            if ($oidObj) {
                $oids[] = Oid::init($oidObj->getOid());
            }
        }

        $this->response = $this->formatResponse($this->snmp->walk($oids));

        $columns = [];
        foreach (self::SNMP_COLUMNS as $name) {
            $columns[$name] = $this->indexResponse($name);
        }

        $entries = [];
        foreach ($columns['srv_port.pon_port'] as $idx => $portRow) {
            $port = (int)$portRow['value'];
            $onu = isset($columns['srv_port.onu_id'][$idx]) ? (int)$columns['srv_port.onu_id'][$idx]['value'] : null;
            if ($onu === null) {
                continue;
            }
            $servicePortId = $this->extractServicePortId($idx);
            $entry = [
                'service_port_id' => $servicePortId,
                'pon_port' => $port,
                'onu_id' => $onu,
                'svlan' => $this->valueOrNull($columns['srv_port.svlan'], $idx),
                'user_vlan' => $this->valueOrNull($columns['srv_port.user_vlan'], $idx),
                'user_vlan_pri' => $this->valueOrNull($columns['srv_port.user_vlan_pri'], $idx),
                'gem_id' => $this->valueOrNull($columns['srv_port.gem_id'], $idx),
                'eth_type' => $this->valueOrNull($columns['srv_port.eth_type'], $idx),
                'flow_type' => null,
                'flow_param' => null,
                'tag_action' => $this->valueOrNull($columns['srv_port.tag_action'], $idx),
                'inner_vid' => $this->valueOrNull($columns['srv_port.inner_vid'], $idx),
                'inner_pri' => $this->valueOrNull($columns['srv_port.inner_pri'], $idx),
                'admin_status' => $this->parsedOrNull($columns['srv_port.admin_status'], $idx),
                'online_state' => $this->parsedOrNull($columns['srv_port.online_state'], $idx),
                'description' => $this->descriptionAt($columns['srv_port.description'], $idx),
                'up_traffic_profile' => $this->normalizeTrafficId($this->valueOrNull($columns['srv_port.up_traffic_id'], $idx)),
                'down_traffic_profile' => $this->normalizeTrafficId($this->valueOrNull($columns['srv_port.down_traffic_id'], $idx)),
                'method' => null,
                'config_status' => null,
            ];

            try {
                $entry['interface'] = $this->buildOntInterface($port, $onu);
            } catch (\Throwable $e) {
                $entry['interface'] = null;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    private function indexResponse($name)
    {
        if (!isset($this->response[$name]) || $this->response[$name]->error()) {
            return [];
        }
        $oidObj = $this->oids->getOidByName($name);
        if (!$oidObj) {
            return [];
        }
        $basePrefix = ltrim($oidObj->getOid(), '.') . '.';
        $out = [];
        foreach ($this->response[$name]->fetchAll() as $row) {
            $oid = ltrim($row->getOid(), '.');
            $idx = $oid;
            if (strpos($oid, $basePrefix) === 0) {
                $idx = substr($oid, strlen($basePrefix));
            }
            $out[$idx] = [
                'value' => $row->getValue(),
                'parsed' => $row->getParsedValue(),
                'hex' => $row->getHexValue(),
            ];
        }
        return $out;
    }

    private function extractServicePortId($index)
    {
        $parts = explode('.', $index);
        return (int)end($parts);
    }

    private function valueOrNull($column, $idx)
    {
        if (!isset($column[$idx])) {
            return null;
        }
        $value = $column[$idx]['value'];
        return $value === '' ? null : $value;
    }

    private function parsedOrNull($column, $idx)
    {
        if (!isset($column[$idx])) {
            return null;
        }
        return $column[$idx]['parsed'];
    }

    private function descriptionAt($column, $idx)
    {
        if (!isset($column[$idx])) {
            return null;
        }
        $hex = $column[$idx]['hex'];
        if ($hex) {
            $bin = @hex2bin(str_replace(' ', '', $hex));
            if ($bin !== false) {
                $bin = trim($bin);
                if ($bin !== '') {
                    return $bin;
                }
            }
        }
        $value = $column[$idx]['value'];
        return $value === '' ? null : $value;
    }

    private function normalizeTrafficId($id)
    {
        if ($id === null || $id === '' || (int)$id <= 0) {
            return null;
        }
        return (int)$id;
    }

    private function buildOntInterface($port, $onu)
    {
        $ifaces = $this->getPhysicalInterfaces();
        foreach ($ifaces as $iface) {
            if ($iface['type'] === 'PON' && (int)$iface['_port'] === $port) {
                $ontIface = $iface;
                $ontIface['type'] = 'ONU';
                $ontIface['parent'] = $iface['id'];
                $ontIface['id'] = $iface['id'] + $onu;
                $ontIface['name'] = $iface['name'] . ":{$onu}";
                $ontIface['_onu'] = $onu;
                $ontIface['_snmp_id'] = $this->encodeSnmpOid($ontIface['name']);
                return $ontIface;
            }
        }
        throw new Exception("Cannot build ONT interface for port={$port} onu={$onu}");
    }
}
