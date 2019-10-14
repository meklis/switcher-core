<?php


namespace SwitcherCore\Modules;



use SwitcherCore\Exceptions\IncompleteResponseException;
use \SnmpWrapper\Response\PoollerResponse;
use \SnmpWrapper\Walker;
use \SwitcherCore\Config\Objects\Model;
use \SwitcherCore\Config\OidCollector;
use \SwitcherCore\Switcher\Objects\WrappedResponse;

abstract class AbstractModule implements ModuleInterface
{
    private $indexesPort = [];
    /**
     * @var WrappedResponse[]
     */
    protected $response;
    /**
     * @var Walker
     */
    protected $walker;

    /**
     * @var OidCollector
     */
    protected $oidsCollector;

    /**
     * @var Model
     */
    protected $model;

    protected $oidNames = [];
    /**
     * @param Model $model
     * @return self
     */
    function setModel(Model $model) {
        $this->model = $model;
        return $this;
    }

    /**
     * @param OidCollector $collector
     * @return self
     */
    function setOidCollector(OidCollector $collector) {
        $this->oidsCollector = $collector;
        return $this;
    }

    /**
     * @param Walker $walker
     * @return self
     */
    function setWalker(Walker $walker) {
        $this->walker = $walker;
        return $this;
    }

    /**
     * @param array $filter
     * @return self
     */
    public abstract function walk($filter = []);

    /**
     * @param array $filter
     * @return self
     */
    public function parse($filter = []) {

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
     */
    protected function formatResponse($response) {
        $formated = [];
        foreach ($response as $resp) {
            $oid = $this->oidsCollector->findOidById($resp->getOid());
            $formated[$oid->getName()] = WrappedResponse::init($resp, $oid->getValues());
        }

        return $formated;
    }
    function getIndexes($ethernetOnly = true) {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        $last_cache_status = $this->walker->getCacheStatus();
        $response = $this->formatResponse($this->walker->useCache(true)->walk([
            $this->oidsCollector->getOidByName('if.Index')->getOid(),
            $this->oidsCollector->getOidByName('if.Type')->getOid(),
        ]));
        $this->walker->useCache($last_cache_status);
        $types = [];
        foreach ($response['if.Type']->fetchAll() as $resp) {
            $types[Helper::getIndexByOid($resp->getOid())] = $resp->getParsedValue();
        }

        foreach ($response['if.Index']->fetchAll() as $resp) {
            if($ethernetOnly && in_array($types[Helper::getIndexByOid($resp->getOid())], ['FE','GE'])) {
                $indexes[Helper::getIndexByOid($resp->getOid())] = $resp->getValue();
            }
        }
        $this->indexesPort = $indexes;
        return $indexes;
    }
    /**
     * @param $name
     * @return WrappedResponse
     * @throws IncompleteResponseException
     */
    protected function getResponseByName($name) {
        if(!isset($this->response[$name])) {
            throw  new IncompleteResponseException("Response with oid $name not found");
        }
        return $this->response[$name];
    }
}