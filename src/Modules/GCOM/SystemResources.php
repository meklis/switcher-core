<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemResources extends GCOMAbstractModule
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
        return [
            'cpu' => [
                'util' => 100 - (int)$this->getResponseByName('resources.cpuIdle')->fetchAll()[0]->getValue(),
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' =>null,
            'memory' => [
                'util' =>  round(100-((int)$this->getResponseByName('resources.memFreeSize')->fetchAll()[0]->getValue() / (int)$this->getResponseByName('resources.memTotalSize')->fetchAll()[0]->getValue() * 100), 2),
                '_free' =>  $this->getResponseByName('resources.memFreeSize')->fetchAll()[0]->getValue(),
                '_total' => $this->getResponseByName('resources.memTotalSize')->fetchAll()[0]->getValue(),
            ],
        ];
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = $this->oids->getOidsByRegex('^resources\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid(), false);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oArray));
        return $this;
    }
}

