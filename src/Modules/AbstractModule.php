<?php


namespace SwitcherCore\Modules;



use DI\Annotation\Inject;
use DI\Container;
use meklis\network\Telnet;
use SnmpWrapper\MultiWalkerInterface;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Switcher\Objects\WrappedResponse;

abstract class AbstractModule
{
    /**
     * @var WrappedResponse[]
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
    private $container;


    /**
     * @Inject
     * @var Telnet
     */
    protected $telnet;

    /**
     * @param array $params
     * @return self
     */
    public abstract function run($params = []);

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
     * @throws \Exception
     */
    protected function formatResponse($response) {
        $formated = [];
        foreach ($response as $resp) {
            $oid = $this->oids->findOidById($resp->getOid());
            $formated[$oid->getName()] = WrappedResponse::init($resp, $oid->getValues());
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
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getModule($moduleName) {
        return $this->container->get("module.{$moduleName}");
    }
}