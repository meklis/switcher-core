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
                        '_onu' => $ontNum,
                        'uni' => null,
                    ],
                    'status' => $statusText,
                    'admin_state' => null,
                    'bind_status'  => null,
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
                $this->snmp->get(
                    [
                        Oid::init($this->oids->getOidByName('ont.adminStatus')->getOid() . ".{$iface['id']}"),
                        Oid::init($this->oids->getOidByName('ont.lastDownReason')->getOid() . ".{$iface['id']}")
                    ]
                )
            );
            if(!$resp || !isset($resp['ont.adminStatus'])) throw new \Exception("Error load admin_status");
            if(!$resp || !isset($resp['ont.lastDownReason'])) throw new \Exception("Error load lastDownReason");
            $data[$iface['id']]['admin_state'] = $resp['ont.adminStatus']->fetchOne()->getParsedValue();
            $data[$iface['id']]['bind_status'] = $resp['ont.lastDownReason']->fetchOne()->getParsedValue();

            if($data[$iface['id']]['status'] == 'Online') {
                $data[$iface['id']]['bind_status'] = 'Online';
            }

            $this->response = [
                $data[$iface['id']]
            ];
        } else {
//            if($cached = $this->getCache("onts_status", true)) {
//                $this->response = $cached;
//                return  $this;
//            }
            $this->fillBindStatuses($data);
//            $this->setCache("onts_status", array_values($data), 10, true);
            $this->response = array_values($data);
        }

        return $this;
    }

    function fillBindStatuses(&$statuses)
    {
        $oid = $this->oids->getOidByName('ont.lastDownReason')->getOid();
        $oids = [];
        foreach ($statuses as $index=>$status) {
            if($status['status'] != 'Online') {
                $oids[] = Oid::init("{$oid}.{$status['interface']['id']}");
            } else {
                $statuses[$index]['bind_status'] = $status['status'];
            }
        }
        if(!$oids) {
            return [];
        }
        $responses = $this->formatResponse($this->snmp->get($oids));
        if(!isset($responses['ont.lastDownReason']) || $responses['ont.lastDownReason']->error()) {
            return  [];
        }
        foreach ($responses['ont.lastDownReason']->fetchAll() as $resp) {
            $index = Helper::getIndexByOid($resp->getOid());
            if($resp->getParsedValue() !== 'Unknown') {
                $statuses[$index]['bind_status'] = $resp->getParsedValue();
            }
        }
        return  $statuses;
    }
}

