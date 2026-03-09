<?php


namespace SwitcherCore\Modules\CData\FD17xxV3;


use Exception;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;

class InterfacesList extends CDataAbstractModuleFD17xxV3
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
        return array_values($this->getInterfaces());
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        return $this;
    }
}

