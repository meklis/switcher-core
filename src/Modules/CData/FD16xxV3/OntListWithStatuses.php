<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntListWithStatuses extends CDataAbstractModuleFD16xxV3
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
        $data = [];
        if(isset($filter['interface']) && $filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            /**
             * @var WrappedResponse[] $resp
             */
            $resp = $this->formatResponse(
                $this->snmp->get(
                    [
                        Oid::init($this->oids->getOidByName('ont.opStatus')->getOid() . ".{$iface['_snmp_id']}"),
                        Oid::init($this->oids->getOidByName('ont.adminStatus')->getOid() . ".{$iface['_snmp_id']}"),
                        Oid::init($this->oids->getOidByName('ont.lastDownReason')->getOid() . ".{$iface['_snmp_id']}")
                    ]
                )
            );
            foreach ($resp as $oidName=>$value) {
                if($value->error()) {
                    throw new Exception($value->error());
                };
                $data[$iface['id']]['interface'] = $iface;
                $name = 'UNKNOWN';
                switch ($oidName) {
                    case 'ont.adminStatus': $name = 'admin_state'; break;
                    case 'ont.lastDownReason': $name = 'bind_status'; break;
                    case 'ont.opStatus': $name = 'status'; break;
                }
                $data[$iface['id']][$name] = $value->fetchOne()->getParsedValue();
            }
            if($data[$iface['id']]['status'] == 'Online') {
                $data[$iface['id']]['bind_status'] = 'Online';
            }
            $this->response = array_values($data);
        } else {
            $resp = $this->formatResponse(
                $this->snmp->walk(
                    [
                        Oid::init($this->oids->getOidByName('ont.opStatus')->getOid()),
                    ]
                )
            );
            if($resp['ont.opStatus']->error()) {
                throw new \Exception($resp['ont.opStatus']->error());
            }

            foreach ($resp['ont.opStatus']->fetchAll() as $resp) {
                $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));

                $data[$iface['id']]['interface'] = $iface;
                $data[$iface['id']]['status'] = $resp->getParsedValue();
                $data[$iface['id']]['admin_state'] = null;
            }
            $this->fillBindStatuses($data);
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

