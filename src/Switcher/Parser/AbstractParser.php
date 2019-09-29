<?php


namespace SnmpSwitcher\Switcher\Parser;



use \SnmpWrapper\Response\PoollerResponse;
use \SnmpWrapper\Walker;
use \SnmpSwitcher\Config\Objects\Model;
use \SnmpSwitcher\Config\OidCollector;
use \SnmpSwitcher\Switcher\Objects\WrappedResponse;

abstract class AbstractParser implements ParserInterface
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
    protected function prepareFilter(&$filter) {
        if(!isset($filter['port'])) $filter['port'] = 0;
        if(!isset($filter['vlan_id'])) $filter['vlan_id'] = 0;
        if(!isset($filter['disa_linkup_diag'])) $filter['disa_linkup_diag'] = true;
        if(!isset($filter['mac'])) $filter['mac'] = '';
        if(!isset($filter['type'])) $filter['type'] = '';
    }

    protected function getIndexes() {
        $indexes = [];
        if($this->indexesPort) {
            return $this->indexesPort;
        }
        $last_cache_status = $this->walker->getCacheStatus();
        $response = $this->formatResponse($this->walker->useCache(true)->walk([
            $this->oidsCollector->getOidByName('if.Index')->getOid()
        ]));
        $this->walker->useCache($last_cache_status);
        foreach ($response['if.Index']->fetchAll() as $resp) {
            $indexes[$this->getIndexByOid($resp->getOid())] = $resp->getValue();
        }
        $this->indexesPort = $indexes;
        return $indexes;
    }
    protected function getIndexByOid($oid) {
        $exploded = explode(".", $oid);
        return $exploded[count($exploded) - 1];
    }
}