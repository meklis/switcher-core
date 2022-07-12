<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
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
        $slots = [];
        foreach ($this->getResponseByName('resources.slot.cpuUtil')->fetchAll() as $d) {
            $slotKey = Helper::getIndexByOid($d->getOid(), 1) . "/" . Helper::getIndexByOid($d->getOid());
            $slots[$slotKey]['name'] = $slotKey;
            $slots[$slotKey]['cpu_util'] = (float)$d->getValue() == -1 ? null : (float)$d->getValue();
        }
        foreach ($this->getResponseByName('resources.slot.temp')->fetchAll() as $d) {
            $slotKey = Helper::getIndexByOid($d->getOid(), 1) . "/" . Helper::getIndexByOid($d->getOid());
            $slots[$slotKey]['name'] = $slotKey;
            $slots[$slotKey]['temp'] = (float)$d->getValue();
        }
        return [
            'disk' => null,
            'interfaces' => null,
            'cards' => array_values($slots),
            'temp' => (float)$this->getResponseByName('resources.temp')->fetchOne()->getValue()/100,
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

