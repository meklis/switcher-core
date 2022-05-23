<?php


namespace SwitcherCore\Modules\CData;


use Exception;
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

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        return  $this->response;
    }

    protected function formate($resp) {
        $data = $this->getResponseByName('pon.ontStatus', $resp);
        if($data->error()) {
            throw new \Exception($data->error());
        }
        $interfaces = [];
        foreach ($data->fetchAll() as $d) {
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
                    case 1:
                        $statusText = 'Online';
                        break;
                    case 2:
                        $statusText = 'Offline';
                        break;
                    case 3:
                        $statusText = 'Alarm';
                        break;
                }
                $interfaces[] = [
                    'interface' => [
                        'name' => $interface['name'] . ":" . $ontNum,
                        'parent' => $interface['id'],
                        'id' =>  $interface['id'] + $ontNum,
                        'xid' => $interface['xid'],
                        'type' => 'ONU',
                        'onu_num' => $ontNum,
                        'uni' => null,
                    ],
                    'status' => $statusText,
                ];
            }
        }
        return $interfaces;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oid = $this->oids->getOidByName('pon.ontStatus');
        $this->response = $this->formate($this->formatResponse($this->snmp->walkBulk([Oid::init($oid->getOid())])));
        return $this;
    }
}

