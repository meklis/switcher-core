<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class SystemResources extends AbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

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
        return [
            'cpu' => [
                'util' => (int)$this->getResponseByName('resources.cpuUtil')->fetchAll()[0]->getValue(),
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' => [
                 'status' => $this->getResponseByName('resources.fanStatus')->fetchAll()[0]->getParsedValue()
            ],
            'memory' => [
                'util' =>  (int)$this->getResponseByName('resources.memUtil')->fetchAll()[0]->getValue(),
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

