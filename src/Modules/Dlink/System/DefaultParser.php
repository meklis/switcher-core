<?php


namespace SwitcherCore\Modules\Dlink\System;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class DefaultParser extends SwitchesPortAbstractModule
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

        return [
            'descr' => $this->getResponseByName('sys.Descr')->fetchOne()->getValue(),
            'uptime' => $this->getResponseByName('sys.Uptime')->fetchOne()->getValueAsTimeTicks(),
            'contact' => $this->getResponseByName('sys.Contact')->fetchOne()->getValue(),
            'name' => $this->getResponseByName('sys.Name')->fetchOne()->getValue(),
            'location' => $this->getResponseByName('sys.Location')->fetchOne()->getValue(),
            'meta' =>  [
                'name' => $this->model->getName(),
                'detect' => $this->model->getDetect(),
                'ports' => $this->model->getPorts(),
                'extra' => $this->model->getExtra(),
                'modules' => $this->model->getModulesList(),
                ]
        ];
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws \Exception
     */
    public function run($filter = [])
    {
        $oids = $this->oids->getOidsByRegex('^sys\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = Oid::init($oid->getOid(),true);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oArray));
        return $this;
    }
}

