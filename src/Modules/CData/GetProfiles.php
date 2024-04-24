<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GetProfiles extends AbstractModule
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
            'sla' => [],
            'alarm' => [],
            'optical_alarm' => [],
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
        if(isset($this->response['profile.alarm.name']) && !$this->response['profile.alarm.name']->error()) {
            foreach ($this->response['profile.alarm.name']->fetchAll() as $d) {
                $data['alarm'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        if(isset($this->response['profile.sla.name']) && !$this->response['profile.sla.name']->error()) {
            foreach ($this->response['profile.sla.name']->fetchAll() as $d) {
                $data['sla'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        if(isset($this->response['profile.optical_alarm.name']) && !$this->response['profile.optical_alarm.name']->error()) {
            foreach ($this->response['profile.optical_alarm.name']->fetchAll() as $d) {
                $data['optical_alarm'][] = $this->convertHexToString($d->getHexValue());
            }
        }
        return $data;
    }

}

