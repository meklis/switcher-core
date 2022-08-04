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

        if($cached = $this->getCache('all_fdb_table', true)) {
            $this->response =  $cached;
            return $this;
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
            if(!$line) continue;
            list($vlanId, $macAddr, $type, $ifaceName) = explode(",", $line);
            if(strpos($ifaceName, "EPON") === false) {
                continue;
            }
            $fdb[] = [
                'interface' => $this->parseInterface($ifaceName),
                'vlan_id' => (int)$vlanId,
                'is_dynamic' => $type == 'dynamic',
                'mac_address' => $macAddr,
            ];
        }
        $this->setCache('all_fdb_table', $fdb, 10);
        $this->response = $fdb;
        return $this;
    }
}

