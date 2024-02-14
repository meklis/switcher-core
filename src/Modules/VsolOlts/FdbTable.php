<?php


namespace SwitcherCore\Modules\VsolOlts;


use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTable extends VsolOltsAbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;


    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            $data = array_filter($data, function ($e) use ($interface) {
                return $e['interface']['id'] == $interface['id'];
            });
        }
        if ($filter['mac']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['mac_address'] == Helper::formatMac($filter['mac']);
            });
        }
        if ($filter['vlan_id']) {
            $data = array_filter($data, function ($e) use ($filter) {
                return $e['vlan_id'] == $filter['vlan_id'];
            });
        }
        return array_values($data);
    }

    function getPretty()
    {
        return $this->response;
    }

    public function run($filter = [])
    {
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if ($iface['type'] != 'ONU') {
                throw new \InvalidArgumentException("Only ONU allowed to set interface");
            }
            $this->response = $this->byOnu($iface);
        } else {
            $this->response = $this->allFdb();
        }
        return $this;
    }

    public function byOnu($iface)
    {

        $this->console->exec("conf t");
        $this->console->exec("interface epon {$iface['_slot']}/{$iface['_port']}");
        $lines = $this->console->exec("show onu {$iface['_onu']} mac-address-table");
        $fdb = [];
        foreach (explode("\n", $lines) as $line) {
            $line = trim($line);
            if (preg_match('/^[0-9]{1,4}[ ]{1,}([0-9]{1,4})[ ]{1,}(\S*)[ ]{1,}(EPON0\/[0-9]{1,3})[ ]{1,}([0-9]{1,4})[ ]{1,}[0-9]{1,20}$/', $line, $m)) {
                $fdb[] = [
                    'vlan_id' => (int)$m[1],
                    'mac_address' => strtoupper($m[2]),
                    'interface' => $iface,
                    'is_dynamic' => null,
                ];
            }
        }
        return $fdb;
    }

    public function allFdb()
    {
        if ($cached = $this->getCache('all_fdb_table', true)) {
            return $cached;
        }
        $data = $this->snmp->walkNext(
            [
                \SnmpWrapper\Oid::init($this->oids->getOidByName('pon.fdbAsStr')->getOid()),
            ]
        );
        if ($data[0]->error) {
            throw new \SNMPException($data[0]->error);
        }
        $str = '';
        foreach ($data[0]->getResponse() as $r) {
            $str .= $r->getValue();
        }
        $fdb = [];
        foreach (explode(";", $str) as $line) {
            $line = trim($line);
            if (!$line) continue;
            list($vlanId, $macAddr, $type, $ifaceName) = explode(",", $line);
            if (strpos($ifaceName, "EPON") === false) {
                continue;
            }
            $fdb[] = [
                'interface' => $this->parseInterface($ifaceName),
                'vlan_id' => (int)$vlanId,
                'is_dynamic' => $type == 'dynamic',
                'mac_address' => $macAddr,
            ];
        }
        $this->setCache('all_fdb_table', $fdb, 30);
        return $fdb;
    }
}

