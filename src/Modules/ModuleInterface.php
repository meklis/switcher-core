<?php
namespace SwitcherCore\Modules;

use Meklis\TelnetOverProxy\Telnet;
use SnmpWrapper\Walker;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\OidCollector;

interface ModuleInterface
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
     * @param array $filter
     * @return $this
     */
    function getPrettyFiltered($filter = []);

    /**
     * @param array $params
     * @return mixed
     */
    function run($params = []);

    /**
     * @param OidCollector $collector
     * @return $this
     */
    function setOidCollector(OidCollector $collector);

    /**
     * @param Walker $walker
     * @return self
     */
    function setWalker(Walker $walker) ;
    /**
     * @param Telnet $telnet
     * @return $this
     */
    function setTelnetConn(?Telnet $telnet) ;
    /**
     * @param \RouterosAPI $api
     * @return $this
     */
    function setRouterOsAPI(?\RouterosAPI $api) ;
}