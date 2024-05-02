<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {

        $useCache = !isset($filter['use_cache']) || $filter['use_cache'] == 'yes';
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $mac = Helper::oid2macArray([
              Helper::getIndexByOid($iface['_llid_id'], 5),
              Helper::getIndexByOid($iface['_llid_id'], 4),
              Helper::getIndexByOid($iface['_llid_id'], 3),
              Helper::getIndexByOid($iface['_llid_id'], 2),
              Helper::getIndexByOid($iface['_llid_id'], 1),
              Helper::getIndexByOid($iface['_llid_id'], ),
            ]);
            return [
                ['interface'=>$iface, 'mac_address' => $mac]
            ];
        } else {
            $response = [];
            foreach ($this->getInterfacesIds($useCache) as $iface) {
                if(!$iface['_llid_id']) continue;
                $response[] = [
                  'interface' => $iface,
                  'mac_address' =>  Helper::oid2macArray([
                      Helper::getIndexByOid($iface['_llid_id'], 5),
                      Helper::getIndexByOid($iface['_llid_id'], 4),
                      Helper::getIndexByOid($iface['_llid_id'], 3),
                      Helper::getIndexByOid($iface['_llid_id'], 2),
                      Helper::getIndexByOid($iface['_llid_id'], 1),
                      Helper::getIndexByOid($iface['_llid_id'], ),
                  ])
                ];
            }
            return  $response;
        }
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

