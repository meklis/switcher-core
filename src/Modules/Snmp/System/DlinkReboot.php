<?php


namespace SwitcherCore\Modules\Snmp\System;


use SwitcherCore\Switcher\Objects\WrappedResponse;
use \SwitcherCore\Modules\AbstractModule;
use \SwitcherCore\Modules\Helper;

class DlinkReboot extends AbstractModule
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
            'descr' => $this->response['sys.Descr']->fetchOne()->getValue(),
            'uptime' => $this->response['sys.Uptime']->fetchOne()->getValueAsTimeTicks(),
            'contact' => $this->response['sys.Contact']->fetchOne()->getValue(),
            'name' => $this->response['sys.Name']->fetchOne()->getValue(),
            'location' => $this->response['sys.Location']->fetchOne()->getValue(),
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
    public function walk($filter = [])
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

