<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceDescriptionsFD16 extends CDataAbstractModule
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

    function getOidsByInterfacesArray($interfaces)
    {
        $oids = [];
        foreach ($interfaces as $iface) {
            $oids = array_merge($oids, [
                \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$iface['xid']}"),
            ]);
        }
        return $oids;
    }

    function getPretty()
    {
        $data = [];
        foreach ($this->response as $resp) {
            $data[] = [
                'interface' => $this->parseInterface(Helper::getIndexByOid($resp->getOid())),
                'description' => $resp->getValue(),
            ];
        }
        return $data;
    }

    public function fetchData($data, $oid_name = 'ont.description')
    {
        $resp = $this->getResponseByName($oid_name, $data);
        if ($resp->error()) {
            throw new \Exception($resp->error());
        }
        return $this->getResponseByName($oid_name, $data)->fetchAll();
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] == 'ONU') {
                $data = $this->formatResponse(
                    $this->snmp->get([\SnmpWrapper\Oid::init($this->oids->getOidByName('ont.description')->getOid() . ".{$interface['id']}")])
                );
                $this->response = $this->fetchData($data);
            } else {
                $data = $this->formatResponse(
                    $this->snmp->get([\SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$interface['xid']}")])
                );
                $this->response = $this->fetchData($data, 'if.Alias');
            }
            return $this;
        }

        $without_arguments = false;
        $physicals = [];
        $onts = [];
        if (!isset($filter['interface_type'])) {
            $without_arguments = true;
        }

        if ($filter['interface_type'] == 'ONU' || $without_arguments) {
            $data = $this->formatResponse(
                $this->snmp->walk([\SnmpWrapper\Oid::init($this->oids->getOidByName('ont.description')->getOid())])
            );
            $this->response = $onts = $this->fetchData($data);
        }
        if ($filter['interface_type'] == 'PHYSICAL' || $without_arguments) {
            $oids = $this->getOidsByInterfacesArray($this->getInterfacesIds());
            $data = $this->formatResponse($this->snmp->get($oids));
            $this->response = $physicals = $this->fetchData($data, 'if.Alias');
        }

        if ($without_arguments) {
            $this->response = array_merge($onts, $physicals);
        }

        return $this;
    }


}

