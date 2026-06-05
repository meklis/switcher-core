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
     * @param array $params
     * @return self
     * @throws Exception
     */
    protected function rawConsoleCommandRun($params = [])
    {
        if (!property_exists($this, 'console') || !$this->console) {
            throw new Exception("Module required console connection");
        }
        if (!isset($params['command'])) {
            throw new Exception("Command parameter is required");
        }

        $command = $params['command'];
        $oldStreamTimeout = null;
        if (strpos($command, "<stream_timeout=") !== false) {
            if (preg_match('/<stream_timeout=([0-9]+(?:\.[0-9]+)?)>/', $command, $m)) {
                if ((float)$m[1] <= 0) {
                    $this->response = [
                        'command' => $params['command'],
                        'output' => '',
                        'success' => "ERROR PARSE STREAM TIMEOUT",
                    ];
                    return $this;
                }
                $oldStreamTimeout = $this->console->getStreamTimeout();
                $this->console->setStreamTimeout((float)$m[1]);
                $command = trim(str_replace($m[0], '', $command));
            } else {
                $this->response = [
                    'command' => $params['command'],
                    'output' => '',
                    'success' => "ERROR PARSE STREAM TIMEOUT",
                ];
                return $this;
            }
        }

        if (strpos($command, "<cr>") !== false) {
            $command = trim(str_replace("<cr>", "", $command));
            $this->console->write($command);
            usleep(100000);
            $this->console->write("");
            $this->console->waitPrompt($this->console->getDeviceHelper()->getPrompt());
            $response = $this->console->getBuffer();
        } elseif (strpos($command, "<prompt=") !== false) {
            if (preg_match("/<prompt=[\"'](.*?)[\"']>/", $command, $m) || preg_match('/<prompt=(.*?)>/', $command, $m)) {
                $command = trim(str_replace($m[0], '', $command));
                $this->console->write($command);
                usleep(300000);
                $this->console->waitPrompt($m[1]);
                $response = $this->console->getBuffer();
            } else {
                $this->response = [
                    'command' => $params['command'],
                    'output' => '',
                    'success' => "ERROR PARSE PROMPT",
                ];
                if ($oldStreamTimeout !== null) {
                    $this->console->setStreamTimeout($oldStreamTimeout);
                }
                return $this;
            }
        } elseif (strpos($command, "<confirm-if=") !== false) {
            if (preg_match('/<confirm-if=(.*?)>/', $command, $m) && $confirmIf = $this->parseRawConsoleConfirmIfArguments($m[1])) {
                $command = trim(str_replace($m[0], '', $command));
                $this->console->write($command);
                usleep(300000);

                if ($this->rawConsoleWaitPrompt(preg_quote($confirmIf['prompt'], '/'), 2)) {
                    $this->console->write($confirmIf['confirm']);
                    $this->console->waitPrompt($this->console->getDeviceHelper()->getPrompt());
                }
                $response = $this->console->getBuffer();
            } else {
                $this->response = [
                    'command' => $params['command'],
                    'output' => '',
                    'success' => "ERROR PARSE PROMPT",
                ];
                if ($oldStreamTimeout !== null) {
                    $this->console->setStreamTimeout($oldStreamTimeout);
                }
                return $this;
            }
        } elseif (strpos($command, "<confirm=") !== false) {
            if (preg_match("/<confirm=[\"'](.*?)[\"']>/", $command, $m) || preg_match('/<confirm=(.*?)>/', $command, $m)) {
                $command = trim(str_replace($m[0], '', $command));
                $this->console->write($command);
                usleep(300000);
                $this->console->write($m[1]);
                $this->console->waitPrompt($this->console->getDeviceHelper()->getPrompt());
                $response = $this->console->getBuffer();
            } else {
                $this->response = [
                    'command' => $params['command'],
                    'output' => '',
                    'success' => "ERROR PARSE PROMPT",
                ];
                if ($oldStreamTimeout !== null) {
                    $this->console->setStreamTimeout($oldStreamTimeout);
                }
                return $this;
            }
        } elseif (isset($params['prompt'])) {
            $response = $this->console->exec($command, true, $params['prompt']);
        } else {
            $response = $this->console->exec($command);
        }

        if ($oldStreamTimeout !== null) {
            $this->console->setStreamTimeout($oldStreamTimeout);
        }

        $this->response = [
            'command' => $params['command'],
            'output' => $response,
            'success' => $this->validResponse($response),
        ];
        return $this;
    }

    protected function parseRawConsoleConfirmIfArguments($arguments)
    {
        if (!preg_match('/^\s*(?:"([^"]*)"|\'([^\']*)\'|([^,]*))\s*,\s*(?:"([^"]*)"|\'([^\']*)\'|(.*))\s*$/', $arguments, $m)) {
            return false;
        }

        $confirm = $m[1] !== '' ? $m[1] : ($m[2] !== '' ? $m[2] : $m[3]);
        $prompt = $m[4] !== '' ? $m[4] : ($m[5] !== '' ? $m[5] : $m[6]);
        if (trim($prompt) === '') {
            return false;
        }

        return [
            'confirm' => trim($confirm),
            'prompt' => trim($prompt),
        ];
    }

    protected function rawConsoleWaitPrompt($prompt, $timeout)
    {
        $lastTimeout = $this->console->getTimeout();
        $lastStreamTimeout = $this->console->getStreamTimeout();
        $this->console->setTimeout($timeout);
        $this->console->setStreamTimeout($timeout);

        try {
            $this->console->waitPrompt($prompt, $timeout);
            return true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Stream timeout') !== false || strpos($e->getMessage(), "Couldn't find the requested") !== false) {
                return false;
            }
            throw $e;
        } finally {
            $this->console->setTimeout($lastTimeout);
            $this->console->setStreamTimeout($lastStreamTimeout);
        }
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $md5;
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
            $key = "NO_CLASS_" . $this->device->getIp() . ":" . $md5;
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
