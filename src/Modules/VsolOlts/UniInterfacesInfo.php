<?php


namespace SwitcherCore\Modules\VsolOlts;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UniInterfacesInfo extends VsolOltsAbstractModule
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
        if(!$filter['interface']) {
            throw new \Exception("Interface is required");
        }
        $iface = $this->parseInterface($filter['interface']);
        if($iface['type'] !== 'ONU') {
            throw new \Exception("Only ONU allowed");
        }
        $countInterfaces = $this->getCountInterfaces($iface);
        if(!$countInterfaces) {
            throw new \Exception("UNI ports not found");
        }


        $response = [];
        for ($num=1; $num <= $countInterfaces; $num++) {
            $this->setUniPort($iface, $num);
            $data = $this->getUniInfo($iface, $num);
            $data['num'] = $num;
            $response[] = $data;
        }
        $this->response = [
            'interface' => $iface,
            'unis' => $response,
        ];
        return $this;
    }

    function setUniPort($iface, $portNum) {
        $resp = $this->snmp->set(
            \SnmpWrapper\Oid::init(
                $this->oids->getOidByName('ont.setUni.ponNum')->getOid(),
                false,
                'Integer',
                $iface['_port']
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        $resp = $this->snmp->set(
            \SnmpWrapper\Oid::init(
                $this->oids->getOidByName('ont.setUni.onuNum')->getOid(),
                false,
                'Integer',
                $iface['_onu']
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        $resp = $this->snmp->set(
            \SnmpWrapper\Oid::init(
                $this->oids->getOidByName('ont.setUni.portNum')->getOid(),
                false,
                'Integer',
                $portNum
            )
        );
        if($resp[0]->error) {
            throw new \Exception("Returned error from device: {$resp[0]->error}");
        }
        return true;
    }

    function getCountInterfaces($iface) {
        return 1;
        $resp = $this->snmp->get([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.interfaceTypePorts')->getOid() . $iface['_snmp_id'])
        ]);
        if($resp[0]->error) {
            throw new \Exception($resp[0]->error);
        }
        $line = $resp[0]->getResponse()[0]->getValue();
        $countIfaces = 0;
        foreach (explode(";", $line) as $elem) {
            if(preg_match('/^(FE|GE)\(([0-9]{1,2})\)$/', trim($elem), $m)) {
                $countIfaces += $m[2];
            }
        }
        return $countIfaces;
    }

    function getUniInfo($iface, $port) {
        $oids = array_map(function ($e) use ($iface, $port) {
            return \SnmpWrapper\Oid::init($e->getOid() . $iface['_snmp_id'] . "." . $port);
        },$this->oids->getOidsByRegex('^ont.uni\..*'));
        $resp = [];
        foreach ($this->formatResponse($this->snmp->get($oids)) as $name=>$response) {
            if($response->error()) {
                throw new \Exception("Err get UNI info: {$response->error()}");
            }
            switch ($name) {
                case 'ont.uni.status':
                    $resp['status'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.adminStatus':
                    $resp['admin_status'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.autoNegot':
                    $resp['auto_negotation'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.flowCtrl':
                    $resp['flow_control'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.vlan.mode':
                    $resp['vlan_mode'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.vlan.pvid':
                    $resp['vlan_pvid'] = $response->fetchOne()->getParsedValue();
                    break;
                case 'ont.uni.vlan.vlan':
                    $resp['vlan'] = $response->fetchOne()->getParsedValue();
                    break;
            }
        }
        return $resp;
    }
}

