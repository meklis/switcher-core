<?php

namespace SwitcherCore\Modules\CData;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


class InterfaceCountersFD12 extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getOidsForOnts()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('ont.counters'));
    }

    function getOidsForPhysical()
    {
        return array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->interfaceCounterOids());
    }

    function getInterfaiceOids($interface_id, $oids)
    {
        return array_map(function ($e) use ($interface_id) {
            return Oid::init($e->getOid() . '.' . $interface_id);
        }, $oids);
    }

    public function run($params = [])
    {
        if ($params['interface']) {
            $interface = $this->parseInterface($params['interface']);
            if ($interface['type'] == 'ONU' && $this->model->getKey() === 'c_data_fd1204sn') {
                $oids = $this->getInterfaiceOids($interface['xid'], $this->getOidsForPhysical());
            } else if ($interface['type'] == 'ONU') {
                $oids = $this->getInterfaiceOids($interface['id'] . '.0.1', $this->getOidsForOnts());
            } else  {
                $oids = $this->getInterfaiceOids($interface['xid'], $this->getOidsForPhysical());
            }
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } elseif ($params['interface_type'] == 'ONU' ) {
            throw new \Exception('Not available (Mass SNMP by ont.counters can reboot device)');
        } else {
            $oids = $this->getOidsForPhysical();
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }

    function getPretty()
    {
        $data = [];
        foreach ($this->response as $oidName => $dt) {
            if ($dt->error()) {
                continue;
            }
            $name = Helper::fromCamelCase(str_replace(["ont.counters.", "if.HC", "if"], "", $oidName));
            foreach ($dt->fetchAll() as $resp) {
                try {
                    if (strpos($oidName, "ont.counters") !== false) {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid(), 2));
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'xid');
                    }
                    $data[$iface['id']]['interface'] = $iface;
                    $data[$iface['id']][$name] = $resp->getValue();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), "not in service card") === false) {
                        throw $e;
                    }
                }
            }
        }
        return array_values($data);
    }

    function getPrettyFiltered($params = [], $fromCache = false)
    {
        return $this->getPretty();
    }
}