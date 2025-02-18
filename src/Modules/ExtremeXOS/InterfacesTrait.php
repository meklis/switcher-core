<?php

namespace SwitcherCore\Modules\ExtremeXOS;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Monolog\Logger;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
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
        if(is_numeric($iface) && $iface < 100) {
            $parseBy = '_port_num';
        }
        if ($parseBy == 'id') {
            if (!isset($ifaces[$iface])) {
                throw new \Exception("Incorrect interface ID - {$iface}");
            }
            return $ifaces[$iface];
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface, $parseBy) {
            return isset($i[$parseBy]) && $i[$parseBy] == $iface;
        });
        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
        $filtered = array_filter($ifaces, function ($i) use ($iface) {
            return isset($i['name']) && $i['name'] == $iface;
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
            Oid::init($this->oids->getOidByName('if.Type')->getOid()),
            Oid::init($this->oids->getOidByName('dot1q.PortIfIndex')->getOid()),
            Oid::init($this->oids->getOidByName('port.loadShare.status')->getOid()),
        ]);

        /**
         * @var  $responses PoollerResponse[]
         */
        $responses = [];
        foreach ($response as $resp) {
            $name = $this->oids->findOidById($resp->getOid());
            if ($resp->getError() && !in_array($name->getName(), ['port.loadShare.status','if.stackStatus', 'dot1q.PortIfIndex'])) {
                throw new \Exception("Error walk {$name->getOid()} on device {$this->device->getIp()}");
            }
            $responses[$name->getName()] = $resp;
        }

        $ifaces = [];
        $ifaceByNames = [];
        foreach ($responses['if.Name']->getResponse() as $r) {
            if (preg_match('/^([0-9]{1,2}):([0-9]{1,3})$/i', trim($r->getValue()), $m)) {
                $ifaceByNames[$r->getValue()] = Helper::getIndexByOid($r->getOid());
                $id = Helper::getIndexByOid($r->getOid());
                $ifaces[Helper::getIndexByOid($r->getOid())] = [
                    'id' => (int)$id,
                    'name' => $r->getValue(),
                    '_snmp_id' => $id,
                    '_port_num' => $m[2],
                    '_slot_num' => $m[1],
                    '_shelf_num' => 0,
                    '_subport' => null,
                    '_type' => 'UNKNOWN',
                    '_sorting' => ($m[1] * 1000) + $m[2],
                    '_lacp_ifaces' => null,
                    'type' => 'UNKNOWN',
                    '_dot1q_id' => null,
                    '_iface_vlans' => null,
                ];
            }
        }

        foreach ($responses['if.Type']->getResponse() as $r) {
            $id = Helper::getIndexByOid($r->getOid());
            if(!isset($ifaces[$id])) {
                continue;
            }
            $type = $this->oids->getOidByName('if.Type')->getValueNameById($r->getValue());
            $ifaces[$id]['type'] = $type;
        }
        if(!$responses['port.loadShare.status']->getError()) {
            foreach ($responses['port.loadShare.status']->getResponse() as $r) {
                if($r->getValue() != 1) {
                    continue;
                }
                $masterIfaceId = Helper::getIndexByOid($r->getOid(),1);
                $slaveIfaceId = Helper::getIndexByOid($r->getOid());
                if($ifaces[$masterIfaceId]['_lacp_ifaces'] == null) {
                    $ifaces[$masterIfaceId]['_lacp_ifaces'] = [];
                }
                $ifaces[$masterIfaceId]['_lacp_ifaces'][] = $ifaces[$slaveIfaceId];
            }
        }


        foreach ($responses['dot1q.PortIfIndex']->getResponse() as $r) {
            if (isset($ifaces[$r->getValue()])) {
                $ifaces[$r->getValue()]['_dot1q_id'] = Helper::getIndexByOid($r->getOid());
            }
        }
        uasort($ifaces, function ($a, $b) {
            if ($a['_sorting'] && $b['_sorting']) {
                return $a['_sorting'] > $b['_sorting'];
            }
            return strcmp($a['name'], $b['name']);
        });

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