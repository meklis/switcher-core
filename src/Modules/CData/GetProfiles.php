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

        $prepared = [];
        if(isset($filter['load_only']) && $filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
            if(in_array("line", $loadOnly)) {
                $prepared[] = $this->oids->getOidByName('profile.line.name');
            }
            if(in_array("srv", $loadOnly)) {
                $prepared[] = $this->oids->getOidByName('profile.srv.name');
            }
            if(in_array("dba", $loadOnly)) {
                $prepared[] = $this->oids->getOidByName('profile.dba.name');
            }
            if(in_array("traffic", $loadOnly)) {
                $prepared[] = $this->oids->getOidByName('profile.traffic.name');
            }
            if(in_array("sla", $loadOnly)) {
                $prepared[] = $this->oids->getOidByName('profile.sla.name');
            }
        } else {
            $prepared = $this->oids->getOidsByRegex('profile\..*');
        }

        $oids = array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $prepared);
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
                $id = Helper::getIndexByOid($d->getOid());
                $data['dba'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.line.name']) && !$this->response['profile.line.name']->error()) {
            foreach ($this->response['profile.line.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['line'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.srv.name']) && !$this->response['profile.srv.name']->error()) {
            foreach ($this->response['profile.srv.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['srv'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.traffic.name']) && !$this->response['profile.traffic.name']->error()) {
            foreach ($this->response['profile.traffic.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['traffic'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.alarm.name']) && !$this->response['profile.alarm.name']->error()) {
            foreach ($this->response['profile.alarm.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['alarm'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.sla.name']) && !$this->response['profile.sla.name']->error()) {
            foreach ($this->response['profile.sla.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['sla'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        if(isset($this->response['profile.optical_alarm.name']) && !$this->response['profile.optical_alarm.name']->error()) {
            foreach ($this->response['profile.optical_alarm.name']->fetchAll() as $d) {
                $id = Helper::getIndexByOid($d->getOid());
                $data['optical_alarm'][] = [
                    'id' => $id,
                    'name' => $this->convertHexToString($d->getHexValue()),
                ];
            }
        }
        return $data;
    }

}

