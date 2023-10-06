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
                $interfaces[$interface['id'] + $ontNum] = [
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
                    'admin_state' => null,
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
        $data = $this->formate($this->formatResponse($this->snmp->walkNext([Oid::init($oid->getOid())])));
        if(isset($filter['interface']) && $filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            /**
             * @var WrappedResponse[] $resp
             */
            $resp = $this->formatResponse(
                $this->snmp->walk(
                    [Oid::init($this->oids->getOidByName('ont.adminStatus')->getOid() . ".{$iface['id']}")]
                )
            );
            if(!$resp || !isset($resp['ont.adminStatus'])) throw new \Exception("Error load admin_status");
            $data[$iface['id']]['admin_state'] = $resp['ont.adminStatus']->fetchOne()->getParsedValue();
            $this->response = [
                $data[$iface['id']]
            ];
        } else {
            if($cached = $this->getCache("onts_status", true)) {
                $this->response = $cached;
                return  $this;
            }
            $resp = $this->formatResponse(
                $this->snmp->walk(
                    [Oid::init($this->oids->getOidByName('ont.adminStatus')->getOid())]
                )
            );
            if($resp['ont.adminStatus']->error()) {
                throw new \Exception($resp['ont.adminStatus']->error());
            }
            foreach ($resp['ont.adminStatus']->fetchAll() as $status) {
                $index = Helper::getIndexByOid($status->getOid());
                if(!isset($data[$index])) continue;
                $data[$index]['admin_state'] = $status->getParsedValue();
            }
            $this->setCache("onts_status", array_values($data), 10, true);
            $this->response = array_values($data);
        }

        return $this;
    }
}

