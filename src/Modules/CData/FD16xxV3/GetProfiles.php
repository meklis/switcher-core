<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GetProfiles extends CDataAbstractModuleFD16xxV3
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
        return $this->response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('profile\..*'));
        $this->response = $this->formatResponse($this->snmp->walkNext($oids));
        return $this;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = [
            'dba' => [],
            'line' => [],
            'srv' => [],
            'traffic' => [],
        ];
        if(isset($this->response['profile.dba.name']) && !$this->response['profile.dba.name']->error()) {
            foreach ($this->response['profile.dba.name']->fetchAll() as $d) {
                $data['dba'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        if(isset($this->response['profile.line.name']) && !$this->response['profile.line.name']->error()) {
            foreach ($this->response['profile.line.name']->fetchAll() as $d) {
                $data['line'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        if(isset($this->response['profile.srv.name']) && !$this->response['profile.srv.name']->error()) {
            foreach ($this->response['profile.srv.name']->fetchAll() as $d) {
                $data['srv'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        if(isset($this->response['profile.traffic.name']) && !$this->response['profile.traffic.name']->error()) {
            foreach ($this->response['profile.traffic.name']->fetchAll() as $d) {
                $data['traffic'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        return $data;
    }

}

