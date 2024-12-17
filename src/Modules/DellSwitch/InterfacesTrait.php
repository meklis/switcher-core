<?php

namespace SwitcherCore\Modules\DellSwitch;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Monolog\Logger;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CacheInterface;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\Objects\WrappedResponse;

trait InterfacesTrait
{
    /**
     * @var array | WrappedResponse[]
     */
    protected $response;

    /**
     * @var OidCollector
     */
    protected $oids;


    /**
     * @var MultiWalkerInterface
     */
    protected $snmp;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Container
     */
    protected $container;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Device
     */
    protected $device;


    function parseInterface($iface, $parseBy = 'id')
    {
        $ifaces = $this->getInterfacesIds();
        if ($parseBy == 'id') {
            if (!isset($ifaces[$iface])) {
                throw new \Exception("Incorrect interface ID");
            }
            return $ifaces[$iface];
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i[$parseBy]) && $i[$parseBy] == $iface;
        });
        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        throw new \Exception("Interface with name {$iface} not found");
    }

    private $_interfaces;

    function getInterfacesIds()
    {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($info = $this->getCache('INTERFACES', true)) {
            $this->_interfaces = $info;
            return $info;
        }
        $response = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Name')->getOid()),
            Oid::init($this->oids->getOidByName('dot1q.PortIfIndex')->getOid()),
        ]);
        $responses = [];
        foreach ($response as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if ($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp->getResponse();
        }

        $ifaces = [];
        foreach ($responses['if.Name'] as $r) {
            if (preg_match('/^(e|g)([0-9]{1,4})$/', $r->getValue(), $m)) {
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => $r->getValue(),
                    'type' => "GE",
                    '_snmp_id' => $id,
                    '_lacp_ifaces' => null,
                    '_dot1q_id' => null,
                ];
            }
            if (preg_match('/^(TenGigabitEthernet|fortyGigE) ([0-9]{1,4})\/([0-9]{1,4})$/', $r->getValue(), $m)) {
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => $r->getValue(),
                    'type' => "TGE",
                    '_snmp_id' => $id,
                    '_lacp_ifaces' => null,
                    '_dot1q_id' => null,
                ];
            }
            if (preg_match('/^(Port-channel) ([0-9]{1,4})$/', $r->getValue(), $m)) {
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => $r->getValue(),
                    'type' => "LACP",
                    '_lacp_ifaces' => null,
                    '_snmp_id' => $id,
                    '_dot1q_id' => null,
                ];
            }
        }
        foreach ($responses['dot1q.PortIfIndex'] as $r) {
            if (isset($ifaces[$r->getValue()])) {
                $ifaces[$r->getValue()]['_dot1q_id'] = Helper::getIndexByOid($r->getOid());
            }
        }


        foreach ($ifaces as $id=>$iface) {
            if($iface['type'] === 'LACP') {
                $ifaces[$id]['_lacp_ifaces'] = array_filter(array_map(function ($e) use ($ifaces) {
                    return isset($ifaces[$e]) ? $ifaces[$e] : null;
                }, $this->getParentIfaceIDsByLACP($id)),
                    function ($i) {
                        return $i !== null;
                    }
                );
            }
        }


        $this->_interfaces = $ifaces;
        $this->setCache("INTERFACES", $ifaces, 600, true);
        return $ifaces;
    }

    /**
     *
     * Method for working with cache.
     * Cache method generate unique prefix key for isolating over device and modules
     *
     * @param $key
     * @return mixed|null
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function getCache($key, $withoutClass = false)
    {
        if (!$this->container->has(CacheInterface::class)) {
            return null;
        }
        $cache = $this->container->get(CacheInterface::class);
        if ($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        return $cache->get($key);
    }

    /**
     *
     * Method for set value to cache
     * Cache method generate unique prefix key for isolating over device and modules
     *
     * @param $key
     * @param mixed $value Any value, not allow streams
     * @param int $timeout Timeouts in sec
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setCache($key, $value, $timeout = -1, $withoutClass = false)
    {
        if (!$this->device->getIp()) {
            throw new NotFoundException("Incorrect injected device, without device");
        }
        if (!$this->container->has(CacheInterface::class)) {
            $this->logger->notice("Cache interface not setted");
            return false;
        }
        if ($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        $this->container->get(CacheInterface::class)->set($key, $value, $timeout);
        return true;
    }


    protected function getParentIfaceIDsByLACP($ifaceId)
    {
        $data = [];
        $resp = $this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.stackStatus')->getOid() . ".{$ifaceId}"),
        ]);

        $name = $this->oids->findOidById($resp[0]->getOid());
        if ($resp[0]->getError()) {
            $this->logger->error("Error get parent ifaces by LACP, oid {$name->getOid()}");
            return [];
        }
        foreach ($resp[0]->getResponse() as $r) {
            $data[] = Helper::getIndexByOid($r->getOid());
        }
        return  $data;
    }

}