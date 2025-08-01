<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


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
                'util' => $this->getResponseByName('resources.cpuUsage')->fetchAll()[0]->getValue(),
            ],
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
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
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

