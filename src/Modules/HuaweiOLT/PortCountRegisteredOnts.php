<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PortCountRegisteredOnts extends HuaweiOLTAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }
    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        $response = $this->getModule('interfaces_list')->run(['interface'=>null])->getPrettyFiltered(['interface'=>null]);
        $return = [];
        foreach ($response as $resp) {
            if(isset($resp['parent'])) {
                $iface = $this->parseInterface($resp['parent']);
            }
            if(!isset($return[$iface['id']]['count'])) {
                $return[$iface['id']]['count'] = 0;
            }
            $return[$iface['id']]['interface'] = $iface;
            $return[$iface['id']]['count']++;
        }
        return array_values($return);
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

