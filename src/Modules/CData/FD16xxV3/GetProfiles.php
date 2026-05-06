<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

/**
 * On FD16xx FW3 the traffic-profile MIB branch (.17409.2.8.11.4.x) is not
 * implemented — SNMP returns NoSuchObject. Traffic profiles are only available
 * through the CLI command `show traffic-profile all`. This module overrides the
 * default GetProfiles behaviour: it loads dba/line/srv/sla/alarm via SNMP and
 * falls back to console for traffic profiles.
 */
class GetProfiles extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = [];

    protected $loadOnly = null;

    function getRaw()
    {
        return $this->response;
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
        $this->loadOnly = isset($filter['load_only']) && $filter['load_only']
            ? array_map('trim', explode(',', $filter['load_only']))
            : null;

        $snmpOidNames = [];
        if ($this->shouldLoad('dba')) {
            $snmpOidNames[] = 'profile.dba.name';
        }
        if ($this->shouldLoad('line')) {
            $snmpOidNames[] = 'profile.line.name';
        }
        if ($this->shouldLoad('srv')) {
            $snmpOidNames[] = 'profile.srv.name';
        }
        if ($this->shouldLoad('sla')) {
            $snmpOidNames[] = 'profile.sla.name';
        }
        $snmpOids = [];
        foreach ($snmpOidNames as $name) {
            try {
                $snmpOids[] = $this->oids->getOidByName($name);
            } catch (\Throwable $e) {
                // OID not configured for this model — skip silently
            }
        }
        $snmpOids = array_filter($snmpOids);

        if ($snmpOids) {
            $oids = array_map(function ($e) {
                return Oid::init($e->getOid());
            }, $snmpOids);
            $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        } else {
            $this->response = [];
        }
        return $this;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [
            'dba' => [],
            'line' => [],
            'srv' => [],
            'traffic' => [],
            'sla' => [],
            'alarm' => [],
            'optical_alarm' => [],
        ];
        $this->fillFromSnmp($data, 'profile.dba.name', 'dba');
        $this->fillFromSnmp($data, 'profile.line.name', 'line');
        $this->fillFromSnmp($data, 'profile.srv.name', 'srv');
        $this->fillFromSnmp($data, 'profile.sla.name', 'sla');

        if ($this->shouldLoad('traffic')) {
            try {
                $data['traffic'] = $this->loadTrafficFromConsole();
            } catch (\Throwable $e) {
                $this->logger->debug("Cannot load traffic profiles via console: " . $e->getMessage());
                $data['traffic'] = [];
            }
        }

        return $data;
    }

    protected function shouldLoad($section)
    {
        if ($this->loadOnly === null) {
            return true;
        }
        return in_array($section, $this->loadOnly, true);
    }

    protected function fillFromSnmp(array &$data, $oidName, $section)
    {
        if (!isset($this->response[$oidName]) || $this->response[$oidName]->error()) {
            return;
        }
        foreach ($this->response[$oidName]->fetchAll() as $d) {
            $id = Helper::getIndexByOid($d->getOid());
            $name = $this->convertHexToString($d->getHexValue());
            $data[$section][] = [
                'id' => (int)$id,
                'name' => $name,
            ];
        }
    }

    protected function loadTrafficFromConsole()
    {
        $output = $this->console->exec('show traffic-profile all');
        return $this->parseTrafficProfileTable($output);
    }

    protected function parseTrafficProfileTable($output)
    {
        $entries = [];
        $lines = preg_split('/\r?\n/', $output);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '' || strpos($line, '---') === 0 || strpos(trim($line), 'Total:') === 0) {
                continue;
            }
            $trimmed = ltrim($line);
            if (preg_match('/^(ID|Profile-name|by\s+profile|\(kbps\)|\(bytes\))/i', $trimmed)) {
                continue;
            }
            $cells = preg_split('/\s+/', $trimmed);
            // Expected columns: ID, Profile-name, CIR, PIR, CBS, PBS, Bind, BindByProfile
            if (count($cells) < 8 || !ctype_digit($cells[0])) {
                continue;
            }
            $entries[] = [
                'id' => (int)$cells[0],
                'name' => $cells[1],
                '_cir' => $this->normalizeNumeric($cells[2]),
                '_pir' => $this->normalizeNumeric($cells[3]),
                '_cbs' => $this->normalizeNumeric($cells[4]),
                '_pbs' => $this->normalizeNumeric($cells[5]),
                '_bind' => $this->normalizeNumeric($cells[6]),
                '_bind_by_profile' => $this->normalizeNumeric($cells[7]),
            ];
        }
        return $entries;
    }

    protected function normalizeNumeric($cell)
    {
        if ($cell === '-' || $cell === '') {
            return null;
        }
        return is_numeric($cell) ? (int)$cell : null;
    }
}
