<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UnregisteredOnts extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

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
        $unregistered = array_filter($this->getModule('pon_onts_status')->run(['load_only' => 'bind_status', 'interface' => null])->getPretty(),
        function ($e) {
            return $e['bind_status'] === 'registered';
        }
        );

        $this->response = array_values(array_map(function ($un) {
            $mac = Helper::oid2mac($un['interface']['_llid_id']);
            unset($un['status']);
            unset($un['admin_state']);
            unset($un['bind_status']);
            $un['_mac_address_hex'] = str_replace(":", "", $mac);
            $un['mac_address'] = $mac;
            $un['_ident'] = $mac;
            return $un;
        }, $unregistered));

        return $this;
    }
}

