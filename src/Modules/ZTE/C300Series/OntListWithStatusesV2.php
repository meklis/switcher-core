<?php


namespace SwitcherCore\Modules\ZTE\C300Series;


use Exception;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class OntListWithStatusesV2 extends ModuleAbstract
{
    protected $fullInterfacesList = [];
    protected $ifaceStatus = [];

    public function run($params = [])
    {
        if (!$this->telnet) {
            throw new Exception("Module required telnet connection");
        }
        if ($this->fullInterfacesList && !$params['interface']) {
            return $this;
        }
        if ($params['interface']) {
            $iface = $this->parseInterface($params['interface']);
            if (isset($this->ifaceStatus[$iface['id']])) {
                return $this;
            }
            $this->ifaceStatus[$iface['id']] = $this->getOneInterfaceOverSNMP($iface);
        } else {
            $cards = array_filter($this->getCardListWithStatuses(), function ($c) {
                return $c['technology'] && $c['num_ports'] > 0 && $c['oper_status'] == 'inService';
            });
            $this->fullInterfacesList = [];
            foreach ($cards as $card) {
                for ($port = 1; $port <= $card['num_ports']; $port++) {
                    try {
                        $resp = [];
                        switch ($card['technology']) {
                            case 'epon':
                                $resp = $this->getStateEPON("epon-olt_{$card['shelf']}/{$card['slot']}/{$port}");
                                break;
                            case 'gpon':
                                $resp = $this->getStateGPON("gpon-olt_{$card['shelf']}/{$card['slot']}/{$port}");
                                break;
                        }
                        $this->fullInterfacesList = array_merge($this->fullInterfacesList, $resp);
                    } catch (\Exception $e) {
                        if(strpos($e->getMessage(), "No related information") === false) {
                            throw $e;
                        }
                        $this->logger->error("error get info for port {$card['shelf']}/{$card['slot']}/{$port} on device {$this->device->getIp()} with message: {$e->getMessage()}");
                    }
                }
            }
        }
        return $this;
    }

    private function getStateEPON($interface)
    {
        $input = $this->telnet->exec("show epon onu state {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show epon onu state {$interface}'");
        $lines = explode("\n", $input);
        $response = [];
        foreach (array_splice($lines, 2) as $line) {
            if (preg_match('/^(.*)[ ]{1,}(Offline|Power Off|Online)[ ]{1,}(idle|complete)[ ]{1,}(.*)$/', trim($line), $matches)) {
                $interface = $this->parseInterface(trim($matches[1]));
                $bindStatus = trim($matches[2]);
                if($bindStatus == 'Power Off') {
                    $bindStatus="PowerOff";
                }
                $response[] = [
                    'interface' => $interface,
                    'status' => strtolower(trim($matches[2])) == 'online' ? 'Online' : 'Offline',
                    'bind_status' => $bindStatus,
                    '_oam_status' => trim($matches[3]),
                    '_mac' => trim($matches[4]),
                    'admin_state' => null,
                ];
            }
        }
        return $response;
    }

    private function getStateGPON($interface)
    {
        $input = $this->telnet->exec("show gpon onu state {$interface}");
        if (!$input) throw new Exception("Empty response on command 'show gpon onu state {$interface}'");
        if (preg_match('/No related information to show/', $input)) {
            throw new Exception('No related information to show');
        }
        $rows = explode("\n", $input);
        $response = [];
        foreach ($rows as $row) {
            $row = trim($row);
            try {
                if (preg_match('/^([0-9].*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*)$/', $row, $match)) {
                    $bindStatus = trim($match[4]);
                    if($bindStatus == 'DyingGasp') {
                        $bindStatus = "PowerOff";
                    }
                    if($bindStatus == 'OffLine') {
                        $bindStatus = "Offline";
                    }
                    if($bindStatus == 'working') {
                        $bindStatus = "Online";
                    }

                    $response[] = [
                        'interface' => $this->parseInterface('gpon-onu_' . trim($match[1])),
                        'admin_state' => $match[2],
                        'status' => strtolower(trim($match[3])) == 'enable' ? 'Online' : 'Offline',
                        'bind_status' => $bindStatus,
                        '_channel' => $match[5],
                    ];
                } elseif (preg_match('/^(gpon-onu_[0-9]{1,3}\/[0-9]{1,3}\/[0-9]{1,3}:[0-9]{1,3})[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)$/', $row, $match)) {
                    $bindStatus = trim($match[5]);
                    if($bindStatus == 'DyingGasp') {
                        $bindStatus = "PowerOff";
                    }
                    if($bindStatus == 'OffLine') {
                        $bindStatus = "Offline";
                    }
                    if($bindStatus == 'working') {
                        $bindStatus = "Online";
                    }
                    $response[] = [
                        'interface' => $this->parseInterface(trim($match[1])),
                        'admin_state' => trim($match[2]),
                        'status' => strtolower(trim($match[3])) == 'enable' ? 'Online' : 'Offline',
                        'bind_status' => $bindStatus,
                        '_channel' => null,
                    ];
                } elseif (preg_match('/^(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*?)[ ]{1,}(.*)$/', $row, $match)) {
                    $bindStatus = trim($match[5]);
                    if($bindStatus == 'working') {
                        $bindStatus = "Online";
                    }
                    if($bindStatus == 'DyingGasp') {
                        $bindStatus = "PowerOff";
                    }
                    if($bindStatus == 'OffLine') {
                        $bindStatus = "Offline";
                    }
                    $response[] = [
                        'interface' => $this->parseInterface('gpon-onu_' . trim($match[1])),
                        'admin_state' => trim($match[2]),
                        'status' => strtolower(trim($match[3])) == 'enable' ? 'Online' : 'Offline',
                        'bind_status' => $bindStatus,
                        '_channel' => null,
                    ];
                }
            } catch (\Exception $e) {
                if(strpos($e->getMessage(), "Error parse port") !== false) {
                    continue;
                }
                throw $e;
            }
        }
        return $response;
    }

    public function getPretty()
    {
        return $this->fullInterfacesList;
    }

    public function getPrettyFiltered($filter = [])
    {
        if ($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            if (isset($this->ifaceStatus[$iface['id']])) {
                return $this->ifaceStatus[$iface['id']];
            } else {
                throw new \Exception("Response by interface with id={$iface['id']} not found");
            }
        }
        return $this->fullInterfacesList;
    }

    public function getOneInterfaceOverSNMP($iface)
    {
        $oidRequests = [];
        $loadingOidNames = [];
        $addOid = function ($oidName) use (&$loadingOidNames, &$oidRequests) {
            $oidRequests[] = $this->oids->getOidByName($oidName);
            $loadingOidNames[] = $oidName;
        };
        if ($iface['_technology'] == 'gpon' && $this->isGponCardsExist() && !preg_match("/fw_1_2$/", $this->model->getKey())) $addOid('gpon.ont.adminState');
        if ($iface['_technology'] == 'gpon' && $this->isGponCardsExist()) $addOid('gpon.ont.phaseState');
        if ($iface['_technology'] == 'epon' && $this->isEponCardsExist()) $addOid('epon.ont.mgmtOnlineStatus');
        if ($iface['_technology'] == 'epon' && $this->isEponCardsExist() && !preg_match("/fw_1_2$/", $this->model->getKey())) $addOid('epon.ont.adminState');
        if ($iface['_technology'] == 'epon' && $this->isEponCardsExist()) $addOid('epon.ont.lastOfflineReason');

        $oids = array_map(function ($e) use ($iface) {
            return \SnmpWrapper\Oid::init($e->getOid() . "." . $iface['_oid_id']);
        }, $oidRequests);
        return $this->formatReponseOverSNMP($this->formatResponse($this->snmp->get($oids)), $loadingOidNames);
    }

    protected function formatReponseOverSNMP($resp, $loadingOidNames)
    {
        $ifaces = [];
        if (in_array('gpon.ont.phaseState', $loadingOidNames)) {
            $data = $this->getResponseByName('gpon.ont.phaseState', $resp);
            if ($data->error()) {
                $this->logger->error($data->error());
            } else {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['status'] = strtolower($d->getParsedValue()) == 'online' ? 'Online' : 'Offline';
                    $ifaces[$xid]['bind_status'] = $d->getParsedValue();
                }
            }
        }
        if (in_array('epon.ont.mgmtOnlineStatus', $loadingOidNames)) {
            $data = $this->getResponseByName('epon.ont.mgmtOnlineStatus', $resp);
            if ($data->error()) {
                throw new \Exception($data->error());
            }
            foreach ($data->fetchAll() as $d) {
                $xid = Helper::getIndexByOid($d->getOid());
                if (!isset($ifaces[$xid])) {
                    $ifaces[$xid] = [
                        'interface' => $this->parseInterface($xid),
                        'status' => null,
                        'bind_status' => null,
                        'admin_state' => null,
                    ];
                }
                $ifaces[$xid]['status'] = strtolower($d->getParsedValue()) == 'online' ? 'Online' : 'Offline';
                $ifaces[$xid]['bind_status'] = $d->getParsedValue();
            }
        }
        if (in_array('epon.ont.lastOfflineReason', $loadingOidNames)) {
            $data = $this->getResponseByName('epon.ont.lastOfflineReason', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    if ($d->getParsedValue() == 0) {
                        continue;
                    }
                    $ifaces[$xid]['bind_status'] = $ifaces[$xid]['status'] === 'Offline' ? $d->getParsedValue() : 'Online';
                }
            }
        }
        if (in_array('gpon.ont.adminState', $loadingOidNames)) {
            $data = $this->getResponseByName('gpon.ont.adminState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid(), 1) . "." . Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['admin_state'] = $d->getParsedValue();
                }
            }
        }
        if (in_array('epon.ont.adminState', $loadingOidNames)) {
            $data = $this->getResponseByName('epon.ont.adminState', $resp);
            if (!$data->error()) {
                foreach ($data->fetchAll() as $d) {
                    $xid = Helper::getIndexByOid($d->getOid());
                    if (!isset($ifaces[$xid])) {
                        $ifaces[$xid] = [
                            'interface' => $this->parseInterface($xid),
                            'status' => null,
                            'bind_status' => null,
                            'admin_state' => null,
                        ];
                    }
                    $ifaces[$xid]['admin_state'] = $d->getParsedValue();
                }
            }
        }
        $c = array_values(array_filter($ifaces, function ($e) {
            return $e['status'] != null || $e['admin_state'] != null || $e['bind_status'] != null;
        }));
        if (count($c) == 0) {
            throw new \Exception("Error get ONT status information, try repeat");
        }

        return $c;
    }
}