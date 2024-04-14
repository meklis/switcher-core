<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * @moduleKey parse_interface
 * Class ParseInterface
 * @package SwitcherCore\Modules\CData\FD16xxV3
 */
class ParseInterface extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        return $this->response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $this->response = $this->parseInterface($filter['interface']);
        return $this;
    }
}

