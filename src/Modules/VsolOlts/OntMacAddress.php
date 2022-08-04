<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [];
        foreach ($this->response as $resp) {
            if($resp->error()) {
                throw new \Exception($resp->error());
            }
            foreach ($resp->fetchAll() as $o) {
                try {
                    $iface = $this->parseInterface(Helper::getIndexByOid($o->getOid()));
                    if($iface['type'] !== 'ONU') continue;
                    $data[] = [
                        'interface' => $iface,
                        'mac_address' => $o->getHexValue(),
                    ];
                } catch (\Exception $e) {}
            }
        }
        return $data;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        //if.PhysAddr
        $oid  = $this->oids->getOidByName('if.PhysAddr')->getOid();
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $this->response = $this->formatResponse(
                $this->snmp->get([Oid::init($oid . "." . $iface['xid'])])
            );
        } else {
            $this->response = $this->formatResponse(
                $this->snmp->walk([Oid::init($oid )])
            );
        }
        return $this;
    }
}

