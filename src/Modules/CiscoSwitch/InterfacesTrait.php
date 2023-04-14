<?php

namespace SwitcherCore\Modules\CiscoSwitch;

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
        if($parseBy == 'id') {
            if(isset($ifaces[$iface])) {
                return $ifaces[$iface];
            }
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i['_port_num']) && $i['_port_num'] == $iface;
        });
        if(count($filtered) > 0) {
            return  array_values($filtered)[0];
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i[$parseBy]) && $i[$parseBy] == $iface;
        });
        if(count($filtered) > 0) {
            return  array_values($filtered)[0];
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
        ]);
        $responses = [];
        foreach ($response as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp->getResponse();
        }

        $ifaces = [];
        foreach ($responses['if.Name'] as $r) {
            if (preg_match('/^Ethernet(.*)$/', $r->getValue(), $m)) {
                $identies = explode("/", $m[1]);

                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => "Eth{$m[1]}",
                    '_snmp_id' => $id,
                    '_port_num' => $identies[1],
                    '_combo_num' => isset($identies[2]) ? $identies[2] : null,
                ];
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

}