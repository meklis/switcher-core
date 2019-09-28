<?php
namespace SnmpSwitcher\Switcher\Parser;

use \SnmpWrapper\Walker;
use \SnmpSwitcher\Config\Objects\Model;
use \SnmpSwitcher\Config\OidCollector;

interface ParserInterface
{
    function parse();
    function getRaw();
    function getPretty();
    /**
     * @param Model $model
     * @return $this
     */
    function setModel(Model $model);

    /**
     * @param OidCollector $collector
     * @return $this
     */
    function setOidCollector(OidCollector $collector);

    /**
     * @param Walker $walker
     * @return $this
     */
    function setWalker(Walker $walker) ;
}