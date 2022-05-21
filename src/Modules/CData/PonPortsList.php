<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsList extends AbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }
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
        },$this->model->getExtraParamByName('interfaces'));
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

