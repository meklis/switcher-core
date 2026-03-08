<?php


namespace SwitcherCore\Modules\CData\FD17xxV3;


use Exception;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsList extends CDataAbstractModuleFD17xxV3
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
        return array_values(array_filter($this->getInterfaces(), function($interface) {
            return $interface['type'] == 'PON';
        }));
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

