<?php

namespace SwitcherCore\Modules\CData\FD5008;

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


    function parseInterface($iface, $parseBy = 'id') {
        $ifaces = $this->getInterfacesIds();
        if($parseBy == 'id') {
            if(isset($ifaces[$iface])) {
                return $ifaces[$iface];
            }
        }
        foreach($ifaces as $num => $_iface) {
            if($iface === $_iface['id']) return $ifaces[$num];
            //if($iface === $_iface['_port_num']) return $ifaces[$num];
            if($iface === $_iface['name']) return $ifaces[$num];
            if($iface === $_iface['_fullname']) return $ifaces[$num];
            if(isset($_iface[$parseBy]) && $_iface[$parseBy] == $iface) return $ifaces[$num];
        }
        throw new \Exception("Interface {$iface} not found");
    }

    private $_interfaces;

    function getInterfacesIds() {
        if ($this->_interfaces) {
            return $this->_interfaces;
        }
        if ($info = $this->getCache('INTERFACES', true)) {
            $this->_interfaces = $info;
            return $info;
        }

        $output = $this->getModule('multi_console_command')
            ->run(['commands' => ['show phy-port slot all']])->getPretty();
        if(!$output[0]) throw new \Exception('Error calling telnet console in InterfacesTrait');

        $strings = explode("\n", $output[0]['output']);
        array_shift($strings);
        $ifaces = [];
        foreach($strings as $string) {
            if(preg_match('/^\s*\d{1,3}\s*\d{1,3}\s*\d{1,3}\s*(ge|fe|pon)(\d{1,3})\/(\d{1,3})\/(\d{1,3})\s*(\d{5,10})/', $string, $m)) {
                $id = $m[5];
                $sh_slot_port = $m[2] . '/' . $m[3] . '/' . $m[4];
                switch($m[1]) {
                    case 'ge': $if_fullname = 'gigabitethernet'; break;
                    case 'fe': $if_fullname = 'fastethernet'; break;
                    case 'pon': $if_fullname = 'xpon'; break;
                }
                $ifaces[$id] = [
                    'id' => $id,
                    'name' => $m[1] . $sh_slot_port,
                    '_fullname' => $if_fullname . $sh_slot_port,
                    '_snmp_id' => $id,
                    '_port_num' => intval($m[4]),
                    '_slot' => intval($m[3]),
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