<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesStatus extends BDcomAbstractModule
{
    /**
     * @var SnmpResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }


    function getPretty()
    {
        $data = [];
        $ifaces = [];
        $data = $this->getResponseByName('if.OperStatus');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($r->getOid()));
                    if($iface['type'] != 'ONU' || $r->getParsedValue() != 'Up') continue;
                    $ifaces[$iface['xid']] = [
                        'interface' => $iface,
                        'unis' => [],
                    ];
                } catch (\Exception $e) {

                }
            }
        } else {
            throw new \Exception("if.OperStatus not returned, but required ({$data->error()})");
        }
        $data = $this->getResponseByName('ont.uni.opStatus');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid(), 1);
                $uni = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['unis'][$uni]['num'] =  $uni;
                $ifaces[$xid]['unis'][$uni]['status'] =  $r->getParsedValue();
            }
        }
        $data = $this->getResponseByName('ont.uni.adminStatus');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid(), 1);
                $uni = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid])) continue;
                $ifaces[$xid]['unis'][$uni]['num'] =  $uni;
                $ifaces[$xid]['unis'][$uni]['admin_state'] =  $r->getParsedValue();
            }
        }
        return array_values(array_map(function ($e){
            if(isset($e['unis'])) {
                $e['unis'] = array_values($e['unis']);
            }
            return $e;
        }, $ifaces));
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oidList[] = $this->oids->getOidByName('if.OperStatus');
        $oidList[] = $this->oids->getOidByName('ont.uni.opStatus');
        $oidList[] = $this->oids->getOidByName('ont.uni.adminStatus');
        $oids = [];
        foreach ($oidList as $oid) {
            $oids[] = $oid->getOid();
        }
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
        }
        $oids = array_map(function ($e) {return \SnmpWrapper\Oid::init($e); }, $oids);
        $this->response = $this->formatResponse($this->snmp->walk($oids));
        return $this;
    }
}

