<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntConfiguration extends HuaweiOLTAbstractModule
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

    protected function formate($resp)
    {
        $return = [];
        try {
            $data = $this->getResponseByName('ont.config.authMethod', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['auth_method'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.config.password', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['password'] = $this->convertHexToString($d->getHexValue());
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('ont.config.timeout', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['timeout'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }

        try {
            $data = $this->getResponseByName('ont.config.lineProfileName', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['line_profile'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.config.serviceProfileName', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['service_profile'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }
        try {
            $data = $this->getResponseByName('ont.config.isolationState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $iface = $this->findIfaceByOid($d->getOid());
                    $return[$iface['id']]['interface'] = $iface;
                    $return[$iface['id']]['isolation'] = $d->getParsedValue();
                }
            }
        } catch (\Exception $e) {
        }

        return array_values($return);
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {

        $oidRequests = [];
        if ($filter['load_only']) {
            $loadOnly = array_map(function ($e) {
                return trim($e);
            }, explode(",", $filter['load_only']));
            if (in_array('auth_method', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.authMethod');
            }
            if (in_array('password', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.password');
            }
            if (in_array('timeout', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.timeout');
            }
            if (in_array('line_profile', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.lineProfileName');
            }
            if (in_array('service_profile', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.serviceProfileName');
            }
            if (in_array('isolation', $loadOnly)) {
                $oidRequests[] = $this->oids->getOidByName('ont.config.isolationState');
            }

        } else {
            $oidRequests = [
                $this->oids->getOidByName('ont.config.authMethod'),
                $this->oids->getOidByName('ont.config.password'),
                $this->oids->getOidByName('ont.config.timeout'),
                $this->oids->getOidByName('ont.config.lineProfileName'),
                $this->oids->getOidByName('ont.config.serviceProfileName'),
                $this->oids->getOidByName('ont.config.isolationState'),
            ];
        }
        $oids = [];
        foreach ($oidRequests as $oid) {
            $oids[] = $oid->getOid();
        }
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->get($oids)
            ));
        } else {
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, $oids);
            $this->response = $this->formate($this->formatResponse(
                $this->snmp->walk($oids)
            ));
        }
        return $this;
    }
}

