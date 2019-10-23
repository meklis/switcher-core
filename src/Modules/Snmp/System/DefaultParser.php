<?php


namespace SwitcherCore\Modules\Snmp\System;


use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class DefaultParser extends AbstractModule
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
        $oids = $this->oidsCollector->getOidsByRegex('^sys\..*');
        $oArray = [];
        foreach ($oids as $oid) {
            $oArray[] = $oid->getOid();
        }
        $this->response = $this->formatResponse($this->walker->walk($oArray));
        return $this;
    }
}

