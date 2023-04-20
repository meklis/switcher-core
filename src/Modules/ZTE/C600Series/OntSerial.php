<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\C600Series\ModuleAbstract;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntSerial extends ModuleAbstract
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        return $this->response;
    }

    protected function formate($resp)
    {
        $ifaces = [];
        $data = $this->getResponseByName('gpon.ont.serial', $resp);
        if (!$data->error()) {
            foreach ($data->fetchAll() as $d) {
                $xid = Helper::getIndexByOid($d->getOid(), 1) . "." .  Helper::getIndexByOid($d->getOid());
                $blocks = explode(":", $d->getHexValue());
                $ifaces[$xid] = [
                    'interface' => $this->parseInterface($xid),
                    'serial' => $this->convertHexToString("{$blocks[0]}:{$blocks[1]}:{$blocks[2]}:{$blocks[3]}") .
                        $blocks[4] . $blocks[5] . $blocks[6] . $blocks[7]
                    ,
                ];
            }
        }
        return array_values($ifaces);
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        $oidRequests = [];
        $oidRequests[] = $this->oids->getOidByName('gpon.ont.serial');
        $oids = [];
        foreach ($oidRequests as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['_oid_id'];
            }, $oids);
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));
        }
        return $this;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }
}

