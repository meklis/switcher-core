<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\CData\FD11XX\CDataAbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PonPortsOptical extends CDataAbstractModule
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
        $optical = $this->oids->getOidsByRegex('pon.portDm.*');
        $oids = [];
        if ($filter['interface']) {
             $interface = $this->parseInterface($filter['interface']);
             if($interface['type'] !== 'PON') {
                 throw new \Exception("Only PON ports supported");
             }
             foreach ($optical as $optOid) {
                 $oids[] = Oid::init("{$optOid->getOid()}.{$interface['xid']}");
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
                $interface = $this->parseInterface(Helper::getIndexByOid($data->getOid()));
                $RETURN[$interface['id']]['interface'] = $interface;
                switch ($oidName) {
                    case 'pon.portDmTemp':
                        $RETURN[$interface['id']]['temp'] = $data->getParsedValue() / 100;
                        break;
                    case 'pon.portDmVoltage':
                        $RETURN[$interface['id']]['voltage'] = $data->getParsedValue() / 100;
                        break;
                    case 'pon.portDmTxPower':
                        $RETURN[$interface['id']]['tx'] = $data->getParsedValue() / 100;
                        break;
                    case 'pon.portDmVendor':
                        $RETURN[$interface['id']]['vendor'] = trim($this->convertHexToString($data->getHexValue()));
                        break;
                    case 'pon.portDmProductName':
                        $RETURN[$interface['id']]['product_name'] = trim($this->convertHexToString($data->getHexValue()));
                        break;
                }
            }

        }
        return array_values($RETURN);
    }
}

