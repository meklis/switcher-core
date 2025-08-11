<?php

namespace SwitcherCore\Modules\Arista;

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
        if($parseBy == 'physical_id') {
            $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
                return isset($i['_physical_id']) && $i['_physical_id'] == $iface;
            });
            if(count($filtered) == 1) {
                return array_values($filtered)[0];
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

    private $_vlans;
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
            Oid::init($this->oids->getOidByName('ent.physicalName')->getOid()),
            Oid::init($this->oids->getOidByName('ent.physicalType')->getOid()),
        ]);
        $responses = [];
        foreach ($response as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if($resp->getError()) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp;
        }
        $ifacesMapping = [];
        foreach ($responses['if.Name']->getResponse() as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if (preg_match('/^Ethernet(\d{1,3})$/', trim($r->getValue()), $m)) {
                $slot = 0;
                $port = $m[1];
                $ifacesMapping[$m[0]] = [
                    'id' => (int) $id,
                    'name' => $m[0],
                    '_snmp_id' => $id,
                    '_port_num' => (int) $port,
                    '_slot' => (int) $slot,
                    '_physical_id' => null,
                ];
            } elseif (preg_match('/^Ethernet((\d{1,3})\/(\d{1,2}))$/', trim($r->getValue()), $m)) {
                $slot = $m[3];
                $port = $m[2];
                $ifacesMapping[$m[0]] = [
                    'id' => (int) $id,
                    'name' => $m[0],
                    '_snmp_id' => $id,
                    '_port_num' => (int) $port,
                    '_slot' => (int) $slot,
                    '_physical_id' => null,
                ];
            } elseif(preg_match('/^Port-Channel(\d{1,3})$/', trim($r->getValue()), $m)) {
                $port = $m[1];
                $ifacesMapping[$m[0]] = [
                    'id' => (int) $id,
                    'name' => $m[0],
                    '_snmp_id' => $id,
                    '_port_num' => (int) $port,
                    '_slot' => 'Port-Channel',
                    '_physical_id' => null,
                ];
            } 
        }
        foreach ($responses['ent.physicalName']->getResponse() as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if(isset($ifacesMapping[$r->getValue()])) {
                $ifacesMapping[$r->getValue()]['_physical_id'] = $id;
            }
            if (preg_match('/^DOM (.*?)\s.*Sensor for (.*)$/', trim($r->getValue()), $m)) {
                if(isset($ifacesMapping[$m[2]])) {
                    $ifacesMapping[$m[2]]['_sensor_ids'][strtolower($m[1])] = $id;
                }
            } elseif (preg_match('/^Xcvr for (.*?): model (.*) type (.*) media (.*)$/', trim($r->getValue()), $m)) {
                if(isset($ifacesMapping[$m[1]])) {
                    $ifacesMapping[$m[1]]['_sfp'] = [
                        'model' => $m[2],
                        'type' => $m[3],
                        'media' => $m[4],
                    ];
                }
            }
        }
        foreach ($responses['ent.physicalType']->getResponse() as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if(isset($ifacesMapping[$r->getValue()])) {
                $ifacesMapping[$r->getValue()]['_physical_id'] = $id;
            }
            if (preg_match('/^DOM (.*?)\s.*Sensor for (.*)$/', trim($r->getValue()), $m)) {
                if(isset($ifacesMapping[$m[2]])) {
                    $ifacesMapping[$m[2]]['_sensor_ids'][strtolower($m[1])] = $id;
                }
            } elseif (preg_match('/^Xcvr for (.*?): model (.*) type (.*) media (.*)$/', trim($r->getValue()), $m)) {
                if(isset($ifacesMapping[$m[1]])) {
                    $ifacesMapping[$m[1]]['_sfp'] = [
                        'model' => $m[2],
                        'type' => $m[3],
                        'media' => $m[4],
                    ];
                }
            }
        }
        $ifaces = [];
        foreach ($ifacesMapping as $name => $data) {
            $ifaces[$data['id']] = $data;
        }
        $this->_interfaces = $ifaces;
        $this->setCache("INTERFACES", $ifaces, 600, true);
        return $ifaces;
    }



    function getVlanIdsMap()
    {
        if ($this->_vlans) {
            return $this->_vlans;
        }
        if ($info = $this->getCache('INTERFACES_VLANS', true)) {
            $this->_vlans = $info;
            return $info;
        }
        $this->getInterfacesIds();
        return $this->_vlans;
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $key;
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $key;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $key;
        }
        $this->container->get(CacheInterface::class)->set($key, $value, $timeout);
        return true;
    }

}