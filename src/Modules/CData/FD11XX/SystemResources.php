<?php


namespace SwitcherCore\Modules\CData\FD11XX;


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
            'temperature' =>  (float)($this->getResponseByName('resources.temperature')->fetchAll()[0]->getValue()),
            'disk' => null,
            'interfaces' => null,
            'cards' => null,
            'fans' => null,
            'memory' => null,
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
            $oArray[] = Oid::init($oid->getOid() . ".0", false);
        }
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

