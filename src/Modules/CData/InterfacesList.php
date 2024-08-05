<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\CData\FD16xxV3\CDataAbstractModuleFD16xxV3;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfacesList extends CDataAbstractModuleFD16xxV3
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
        return array_values($this->model->getExtraParamByName('interfaces'));
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

