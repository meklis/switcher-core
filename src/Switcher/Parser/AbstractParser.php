<?php


namespace Switcher\Switcher\Parser;



use SnmpWrapper\Walker;
use Switcher\Config\Objects\Model;
use Switcher\Config\OidCollector;

abstract class AbstractParser implements ParserInterface
{
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
    public abstract function parse($filter = []);

    /**
     * @return array
     */
    public abstract function getRaw();

    /**
     * @return array
     */
    public abstract function getSwitchData();

}