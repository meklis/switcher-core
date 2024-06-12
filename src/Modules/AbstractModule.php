<?php


namespace SwitcherCore\Modules;



use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Monolog\Logger;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Trap;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Switcher\CacheInterface;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\Objects\WrappedResponse;

abstract class AbstractModule
{
    /**
     * @var array | WrappedResponse[]
     */
    protected $response;

    /**
     * @Inject
     * @var OidCollector
     */
    protected $oids;


    /**
     * @Inject
     * @var MultiWalkerInterface
     */
    protected $snmp;

    /**
     * @Inject
     * @var Model
     */
    protected $model;

    /**
     * @Inject
     * @var Container
     */
    protected $container;


    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    /**
     * @Inject
     * @var Device
     */
    protected $device;

    /**
     * @param array $params
     * @return self
     */
    public abstract function run($params = []);

    public function trap(Trap  $trap, $data)
    {
        throw new \Exception("Not implemented catching trap in ".get_class($this));
    }

    /**
     * @return array
     */
    public function getRaw() {
        return  $this->response;
    }

    /**
     * @return array
     */
    public abstract function getPretty();
    public abstract function getPrettyFiltered($filter = []);

    /**
     * @param PoollerResponse[] $response
     * @return WrappedResponse[]
     *
     * @throws Exception
     */
    protected function formatResponse($response) {
        $formated = [];
        foreach ($response as $resp) {
            $oid = $this->oids->findOidById($resp->getOid());
            if(isset($formated[$oid->getName()])) {
                $formated[$oid->getName()]->addElements($resp, $oid->getValues());
            } else {
                $formated[$oid->getName()] = WrappedResponse::init($resp, $oid->getValues());
            }
        }

        return $formated;
    }

    /**
     * @param $name
     * @return WrappedResponse
     * @throws IncompleteResponseException
     */
    protected function getResponseByName($name, &$sourceMap = null) {
        if($sourceMap) {
            if(!isset($sourceMap[$name])) {
                throw  new IncompleteResponseException("Response with oid $name not found");
            }
            return $sourceMap[$name];
        }
        if(!isset($this->response[$name])) {
            throw  new IncompleteResponseException("Response with oid $name not found");
        }
        return $this->response[$name];
    }
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * @param $moduleName
     * @return AbstractModule
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getModule($moduleName) {
        return $this->container->get("module.{$moduleName}");
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
    protected function getCache($key, $withoutClass = false) {
        if(!$this->container->has(CacheInterface::class)) {
            return null;
        }
        $cache = $this->container->get(CacheInterface::class);
        $md5 = md5($key);
        if($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $md5;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $md5;
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
    protected function setCache($key, $value, $timeout = -1, $withoutClass = false) {
        if(!$this->device->getIp()) {
            throw new NotFoundException("Incorrect injected device, without device");
        }
        if(!$this->container->has(CacheInterface::class)) {
            $this->logger->notice("Cache interface not setted");
            return false;
        }
        $md5 = md5($key);
        if($withoutClass) {
            $key = "_" . $this->device->getIp() . ":" . $md5;
        } else {
            $key = get_class($this) . ":" . $this->device->getIp() . ":" . $md5;
        }
        $this->container->get(CacheInterface::class)->set($key, $value, $timeout);
        return  true;
    }

    function convertHexToString($string, $trimNulls = false) {
        if($trimNulls) {
            $string = rtrim($string, "0");
        }
        $symbols = explode(":", $string);
        $str = '';
        foreach ($symbols as $symbol) {
            if(!hexdec($symbol)) continue;
            $char = Helper::hexToStr($symbol);
            if(!mb_detect_encoding($char, 'ASCII', true)) {
                continue;
            }

            $str .= $char;
        }
        return $str;
    }
    function convertHexToStringWithoutDelimiter($string, $trimNulls = false) {
        if($trimNulls) {
            $string = rtrim($string, "0");
        }
        $symbols = str_split($string, 2);
        $str = '';
        foreach ($symbols as $symbol) {
            if(!hexdec($symbol)) continue;
            $char = Helper::hexToStr($symbol);
            if(!mb_detect_encoding($char, 'ASCII', true)) {
                continue;
            }

            $str .= $char;
        }
        return $str;
    }


    /**
     * @param PoollerResponse[] $responses
     * @return void
     */
    protected function checkSnmpRespError($responses) {
        foreach ($responses as $response) {
            if($response->error) {
                throw new \SNMPException($response->error);
            }
        }
        return;
    }

    function getInterfaceCountersOids(): array
    {
        return [
            $this->oids->getOidByName('if.InErrors'),
            $this->oids->getOidByName('if.OutErrors'),
            $this->oids->getOidByName('if.InDiscards'),
            $this->oids->getOidByName('if.OutDiscards'),
            $this->oids->getOidByName('if.HCInOctets'),
            $this->oids->getOidByName('if.HCOutOctets'),
//            $this->oids->getOidByName('if.HCInMulticastPkts'),
//            $this->oids->getOidByName('if.HCOutMulticastPkts'),
//            $this->oids->getOidByName('if.HCInBroadcastPkts'),
//            $this->oids->getOidByName('if.HCOutBroadcastPkts'),
        ];
    }
}
