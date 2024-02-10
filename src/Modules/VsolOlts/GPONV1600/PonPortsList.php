<?php


namespace SwitcherCore\Modules\VsolOlts\GPONV1600;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsList extends VsolOltsAbstractModule
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
        return array_values(array_filter($this->getPhysicalInterfaces(), function ($e) {
            return  $e['type'] == 'PON';
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

