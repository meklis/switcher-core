<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\CData\FD11XX\CDataAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReasons extends CDataAbstractModule
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
        $optical = $this->oids->getOidsByRegex('ont.downReason');
        $oids = [];
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if($interface['type'] !== 'ONU') {
                throw new \Exception("Only ONTs supported");
            }
            foreach ($optical as $optOid) {
                $oids[] = Oid::init("{$optOid->getOid()}.{$interface['_snmp_id']}");
            }
        } else {
            foreach ($optical as $optOid) {
                $oids[] = Oid::init("{$optOid->getOid()}");
            }
        }
        $this->response = $this->process($this->formatResponse($this->snmp->walk($oids)));
        return $this;
    }

    /**
     * @param WrappedResponse[] $response
     * @return array
     * @throws Exception
     */
    function process($response) {
        $RETURN = [];
        foreach ($response as $oidName => $resp) {
            if($resp->error()) {
                throw new \Exception($resp->error());
            }
            foreach ($resp->fetchAll() as $data) {
                $interface = $this->parseInterface((Helper::getIndexByOid($data->getOid(), 1) * 1000) + Helper::getIndexByOid($data->getOid()));
                $RETURN[$interface['id']]['interface'] = $interface;
                switch ($oidName) {
                    case 'ont.downReason':
                        $RETURN[$interface['id']]['last_down_reason'] = trim($data->getValue()) ? trim($data->getValue()) : null;
                        break;
                }
            }
        }
        return array_values($RETURN);
    }
}

