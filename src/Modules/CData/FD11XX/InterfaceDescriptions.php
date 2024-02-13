<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceDescriptions extends CDataAbstractModule
{
    /**
     * @var SnmpResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }

    public function fetchData($data, $oid_name = 'ont.name')
    {
        $resp = $this->getResponseByName($oid_name, $data);
        if ($resp->error()) {
            throw new \Exception($resp->error());
        }
        return $this->getResponseByName($oid_name, $data)->fetchAll();
    }

    function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            foreach ($dt as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                if ($oidName == 'ont.name') {
                    $ponNum = Helper::getIndexByOid($resp->getOid(), 1);
                    $id = ($ponNum * 1000) + $id;
                }
                $interface = $this->parseInterface($id, 'id');
                $data[] = [
                    'interface' => $interface,
                    'description' => $this->convertHexToString($resp->getHexValue()),
                ];
            }
        }
        return $data;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface'] );
            if ($interface['type'] == 'ONU') {
                $oid_name = 'ont.name';
                $data = $this->formatResponse(
                    $this->snmp->get([\SnmpWrapper\Oid::init($this->oids->getOidByName($oid_name)->getOid() . "{$interface['_snmp_id']}"),])
                );
                $this->response[$oid_name] = $this->fetchData($data);
            } else {
                $oid_name = 'if.Alias';
                $data = $this->formatResponse(
                    $this->snmp->get([\SnmpWrapper\Oid::init($this->oids->getOidByName($oid_name)->getOid() . ".{$interface['xid']}"),])
                );
                $this->response[$oid_name] = $this->fetchData($data, $oid_name);
            }
            return $this;
        }

        $without_arguments = false;
        if (!isset($filter['interface_type'])) {
            $without_arguments = true;
        }

        if ($filter['interface_type'] == 'ONU' || $without_arguments) {
            $oid_name = 'ont.name';
            $data = $this->formatResponse(
                $this->snmp->walkNext([\SnmpWrapper\Oid::init($this->oids->getOidByName($oid_name)->getOid()),])
            );
            $this->response[$oid_name] = $this->fetchData($data);
        }
        if ($filter['interface_type'] == 'PHYSICAL' || $without_arguments) {
            $oid_name = 'if.Alias';
            $data = $this->formatResponse(
                $this->snmp->walkNext([\SnmpWrapper\Oid::init($this->oids->getOidByName($oid_name)->getOid()),])
            );
            $this->response[$oid_name] = $this->fetchData($data, $oid_name);
        }

        return $this;
    }
}

