<?php


namespace SwitcherCore\Modules\CData;


use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

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
        $data = $this->getResponseByName('pon.ontStatus')->fetchAll();
        $interfaces = [];
        foreach ($data as $d) {
            $interface = $this->parseInterface(Helper::getIndexByOid($d->getOid()));
            $onts = explode(":", $d->getHexValue());
            foreach ($onts as $k => $ont) {
                $ontNum = $k + 1;
                $status = hexdec($ont);
                if ($status === 0) {
                    continue;
                }
                $statusText = '';
                switch ($status) {
                    case 1: $statusText = 'Online'; break;
                    case 2: $statusText = 'Offline'; break;
                    case 3: $statusText = 'Alarm'; break;
                }
                $interfaces[] = [
                    '_interface' => [
                        'name' => $interface['name'],
                        'id' => $interface['id'],
                        'xid' => $interface['xid'],
                        'type' => 'ONU',
                        'onu_num' => $ontNum,
                        'onu_id' => $interface['id'] + $ontNum,
                        'uni' => null,
                    ],
                    'interface' => $interface['name'] . ":" . $ontNum,
                    'status' =>  $statusText,
                ];
            }
        }
        return $interfaces;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws \Exception
     */
    public function run($filter = [])
    {
//        if($this->cache && $this->cache->get($this->obj->model->get))
        $oid = $this->obj->oidCollector->getOidByName('pon.ontStatus');
        $this->response = $this->formatResponse($this->obj->walker->walk([Oid::init($oid->getOid())]));
        return $this;
    }
}

