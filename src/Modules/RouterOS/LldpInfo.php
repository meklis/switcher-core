<?php

namespace SwitcherCore\Modules\RouterOS;



use SwitcherCore\Modules\Helper;

class LldpInfo extends \SwitcherCore\Modules\General\LldpInfo
{
    use InterfacesTrait;
    protected function formate()
    {
        $response = [
            'local' => [
                'chassis_id' => null,
                'ports' => null,
            ],
            'remotes' => []
        ];
        if (isset($this->response['lldp.locChassisId'])) {
            if ($error = $this->getResponseByName('lldp.locChassisId')->error()) {
                $this->logger->error($error);
            } else {
                $response['local']['chassis_id'] =  $this->getResponseByName('lldp.locChassisId')->fetchOne()->getHexValue();
            }
        }
        if (isset($this->response['lldp.locPortId'])) {
            if ($error = $this->getResponseByName('lldp.locPortId')->error()) {
                $this->logger->error($error);
            } else {
                $ports = [];
                foreach ($this->getResponseByName('lldp.locPortId')->fetchAll() as $dt) {
                    $id = Helper::getIndexByOid($dt->getOid());
                    $ports[] = [
                        'port_id' => (int)$id,
                        'name' => $this->removeNullBytes($dt->getHexValue()),
                        'interface' => $this->parseInterface($id),
                    ];
                }
            }
            $response['local']['ports'] = $ports;
        }

        $remotes = [];
        if (isset($this->response['lldp.remChassisId'])) {
            if ($error = $this->getResponseByName('lldp.remChassisId')->error()) {
                $this->logger->error($error);
            } else {
                foreach ($this->getResponseByName('lldp.remChassisId')->fetchAll() as $dt) {
                    try {
                        $id = Helper::getIndexByOid($dt->getOid());
                        $remotes[$id] = [
                            'loc_interface' => $this->parseInterface($id),
                            'rem_chassis_id' => $dt->getHexValue(),
                            'rem_interface' => null,
                            '_rem_port_id' => null,
                        ];
                    } catch (\Exception $e) {}
                }
            }
        }
        if (isset($this->response['lldp.remPortId'])) {
            if ($error = $this->getResponseByName('lldp.remPortId')->error()) {
                $this->logger->error($error);
            } else {
                foreach ($this->getResponseByName('lldp.remPortId')->fetchAll() as $dt) {
                    try {
                        $id = Helper::getIndexByOid($dt->getOid());
                        $remotes[$id]['loc_interface'] = $this->parseInterface($id);
                        $remotes[$id]['rem_interface'] = $this->removeNullBytes($dt->getHexValue());
                        $remotes[$id]['_rem_port_id'] = $this->getPortId($remotes[$id]['rem_chassis_id'], $this->removeNullBytes($dt->getHexValue()));
                        if (!isset($remotes[$id]['rem_chassis_id'])) {
                            $remotes[$id]['rem_chassis_id'] = null;
                        }
                    } catch (\Exception $e) {}
                }
            }
        }
        $response['remotes'] = array_values($remotes);
        return $response;
    }
}
