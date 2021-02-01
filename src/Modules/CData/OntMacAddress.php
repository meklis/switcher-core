<?php


namespace SwitcherCore\Modules\CData;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }
    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $response = $this->getResponseByName('pon.countRegisteredOnts')->fetchAll();
        $return = [];
        foreach ($response as $resp) {
            $return[] = [
                'iface' => $this->parseInterface(Helper::getIndexByOid($resp->getOid())),
                'count' => $resp->getValue(),
            ];
        }
        return $return;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws \Exception
     */
    public function run($filter = [])
    {

        $oid = $this->obj->oidCollector->getOidByName('pon.countRegisteredOnts');
        $this->response = $this->formatResponse($this->obj->walker->walk([Oid::init($oid->getOid())]));
        return $this;
    }
}

