<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PortCountRegisteredOnts extends VsolOltsAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        /*$response = $this->getResponseByName('pon.portCountOnu')->fetchAll();
        $return = [];
        $pon_port_arr = [];
        foreach ($this->getPhysicalInterfaces() as $iface) {
            if($iface['type'] == 'PON'){
                $pon_port_arr[$iface['_port']] = $iface;
            }
        }
        foreach ($response as $resp) {
            $id_from_oid = Helper::getIndexByOid($resp->getOid());
            $pon_iface = $pon_port_arr[$id_from_oid];
            $interface = $this->parseInterface($pon_iface['id']);
            $return[] = [
                'interface' => $interface,
                'count' => (int)$resp->getValue(),
            ];
        }
        return $return;*/
        return $this->response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $tmp_data = [];
        foreach ($this->getOntInterfaces() as $ont) {
            if (isset($ont['parent'])) {
                if (!isset($tmp_data[$ont['_port']]['count'])) {
                    $tmp_data[$ont['_port']]['count'] = 1;
                } else {
                    $tmp_data[$ont['_port']]['count'] += 1;
                }
            }
        }
        $i = 0;
        foreach ($this->getPhysicalInterfaces() as $physicalInterface) {
            if($physicalInterface['type'] != 'PON') continue;
            if (isset($tmp_data[$physicalInterface['_port']])) {
                $data[$i]['interface'] = $physicalInterface;
                $data[$i++]['count'] = $tmp_data[$physicalInterface['_port']]['count'];
            }
        }
        $this->response = array_values($data);
        /*$oid = $this->oids->getOidByName('pon.portCountOnu');
        $this->response = $this->formatResponse($this->snmp->walk([Oid::init($oid->getOid())]));*/
        return $this;
    }
}

