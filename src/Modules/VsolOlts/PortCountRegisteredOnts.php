<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PortCountRegisteredOnts extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }
    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $response = $this->getResponseByName('pon.portCountOnu')->fetchAll();
        $return = [];
        foreach ($response as $resp) {
            $interface = $this->parseInterface("." . Helper::getIndexByOid($resp->getOid()));
            $return[] = [
                'interface' => $interface,
                'count' => (int)$resp->getValue(),
            ];
        }
        return $return;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oid = $this->oids->getOidByName('pon.portCountOnu');
        $this->response = $this->formatResponse($this->snmp->walk([Oid::init($oid->getOid())]));
        return $this;
    }
}

