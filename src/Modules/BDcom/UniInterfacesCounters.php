<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesCounters extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
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
        foreach ($this->response as $name => $r) {
            if($r->error()) {
               continue;
            }
            foreach ($r->fetchAll() as $resp) {
                $xid = Helper::getIndexByOid($resp->getOid(), 1);
                $uni = Helper::getIndexByOid($resp->getOid());
                if($uni > 32) continue;
                $name = Helper::fromCamelCase(str_replace("ont.uni.stat", "", $name));
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['unis'][$uni]['num'] =  (int)$uni;
                $ifaces[$xid]['unis'][$uni][$name] =  (int)$resp->getValue();
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
        $oidList = $this->oids->getOidsByRegex('^ont.uni.stat.*');
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

