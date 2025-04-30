<?php

namespace SwitcherCore\Modules\MikrotikCRS;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;

class LldpInfo extends \SwitcherCore\Modules\General\LldpInfo
{
    use InterfacesTrait;


    protected $api;

    function __construct(\RouterosAPI $api)
    {
        $this->api = $api;
    }

    public function getNeighborByApi($filter = [])
    {
        $params = [];
        $resp = $this->api->comm("/ip/neighbor/print", $params);
        if(!$resp) {
            return [];
        } elseif (isset($resp['!trap'][0]['message'])) {
            throw  new Exception("RouterOS api returned error - ".$resp['!trap'][0]['message']);
        }
        $remotes = [];
        foreach ($resp as $item) {
            $ifaceName = str_replace([',bridge', ',router'], '', $item['interface']);
            $iface =$this->parseInterface($ifaceName, 'name');
            /**
             * {
             * "loc_interface": {
             * "id": 5,
             * "name": "sfp-sfpplus5",
             * "type": "FE",
             * "_snmp_id": "5",
             * "_dot1q_id": "21"
             * },
             * "rem_chassis_id": "00:55:B1:DD:E1:CD",
             * "rem_interface": null,
             * "_rem_port_id": null
             * },
             */
            if($item['ipv6'] == 'true') {
                continue;
            }
            $remotes[] = [
               'loc_interface' => $iface,
               'rem_chassis_id' => $item['mac-address'],
               'rem_interface' => null,
               '_rem_port_id' => null,
            ];
        }

        return $remotes;
    }


    protected function formate()
    {
        $response = [
            'local' => [
                'chassis_id' => null,
                'ports' => null,
            ],
            'remotes' => $this->getNeighborByApi(),
        ];
        if (isset($this->response['lldp.locChassisId'])) {
            if ($error = $this->getResponseByName('lldp.locChassisId')->error()) {
                $this->logger->error($error);
            } else {
                $response['local']['chassis_id'] =  $this->getResponseByName('lldp.locChassisId')->fetchOne()->getHexValue();
            }
        }
        if (isset($this->response['lldp.locPortId'])) {
            $ports = [];
            if ($error = $this->getResponseByName('lldp.locPortId')->error()) {
                $this->logger->error($error);
            } else {
                foreach ($this->getResponseByName('lldp.locPortId')->fetchAll() as $dt) {
                    $id = Helper::getIndexByOid($dt->getOid());
                    try {
                        $iface = $this->parseInterface($id);
                        $ports[] = [
                            'port_id' => (int)$id,
                            'name' => $this->removeNullBytes($dt->getHexValue()),
                            'interface' => $iface,
                        ];
                    } catch (\Exception $e) {}
                }
            }
            $response['local']['ports'] = $ports;
        }

        return $response;
    }

    function getPretty()
    {
        return $this->formate();
    }

    function removeNullBytes($hex)
    {
        return str_replace(":00", '', $hex);
    }

    function getPortId($chassisId, $portIdent)
    {
        if (!$chassisId) return null;
        if (!$portIdent) return null;
        try {
            return hexdec(str_replace(':', '', $portIdent)) - hexdec(str_replace(':', '', $chassisId));
        } catch (\Exception $e) {
            return null;
        }
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }


    public function run($filter = [])
    {
        Helper::prepareFilter($filter);
        $oids = [];
        if (isset($filter['load_only'])) {
            $loadOnly = explode(",", $filter['load_only']);
            if (in_array("loc_chassis", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.locChassisId');
            }
            if (in_array("loc_ports", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('lldp.locPortId');
            }
        } else {
            $oids = $this->oids->getOidsByRegex('lldp\..*');
        }

        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid->getOid());
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}
