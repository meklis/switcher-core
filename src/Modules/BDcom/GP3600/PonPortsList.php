<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsList extends BDcomAbstractModule
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
        return array_map(function($e){
            if(!isset($e['pontype'])) {
                $e['pontype'] = null;
            }
            return $e;
        },$this->getPhysicalInterfaces());
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

