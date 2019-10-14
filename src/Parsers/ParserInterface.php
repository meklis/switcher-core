<?php
namespace SwitcherCore\Parsers;

use \SnmpWrapper\Walker;
use \SwitcherCore\Config\Objects\Model;
use \SwitcherCore\Config\OidCollector;

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