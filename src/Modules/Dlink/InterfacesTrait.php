<?php

namespace SwitcherCore\Modules\Dlink;

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
            Oid::init($this->oids->getOidByName('if.Descr')->getOid())
        ])[0];
        if ($response->getError()) {
            throw new \Exception($response->getError());
        }
        $ifaces = [];

        foreach ($response->getResponse() as $r) {
            if (preg_match('/^Ether[Nn]et Port on unit ([0-9]{1,2}), port[ :]([0-9]{1,4})$/', $r->getValue(), $m)) {
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => "{$m[1]}/{$m[2]}",
                    '_snmp_id' => $id,
                    '_unit' => $m[1],
                    '_port' => $m[2],
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

    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        if($cached = $this->getCache('port_indexes', true)) {
            return $cached;
        }
        $response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('if.Name')->getOid()),
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
        ]));
        $types = [];
        foreach ($response['if.Type']->fetchAll() as $resp) {
            $types[Helper::getIndexByOid($resp->getOid())] = $resp->getParsedValue();
        }

        foreach ($response['if.Name']->fetchAll() as $resp) {
            if(isset($types[Helper::getIndexByOid($resp->getOid())]) &&
                $ethernetOnly &&
                in_array($types[Helper::getIndexByOid($resp->getOid())], ['FE','GE']) &&
                strpos($resp->getValue(), "ch") !== 0
            ) {
                $indexes[Helper::getIndexByOid($resp->getOid())] = [
                    'id' =>  Helper::getIndexByOid($resp->getOid()),
                    'name' => $resp->getValue(),
                    '_key' => Helper::getIndexByOid($resp->getOid()),
                    'type' => $types[Helper::getIndexByOid($resp->getOid())],
                ];
            }
        }
        $this->indexesPort = $indexes;
        $this->setCache('port_indexes', $indexes, 600, true);
        return $indexes;
    }

    function parseInterface($interface) {
        $interfaces = $this->getIndexes();
        if(preg_match('/^[0-9]{1,}$/', $interface)) {
            if(isset($interfaces[$interface])) {
                return $interfaces[$interface];
            } else {
                throw new \Exception("Interface with id '$interface' not found in device");
            }
        } else {
            foreach ($interfaces as $iface) {
                if($iface['name'] === $interface) {
                    return $iface;
                }
            }
            throw new \Exception("Interface with name|key '$interface' not found in device");
        }
    }

}