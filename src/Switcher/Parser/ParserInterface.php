<?php
namespace Switcher\Switcher\Parser;

use SnmpWrapper\Walker;
use Switcher\Config\Objects\Model;
use Switcher\Config\OidCollector;

interface ParserInterface
{
    function parse();
    function getRaw();
    function getSwitchData();
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