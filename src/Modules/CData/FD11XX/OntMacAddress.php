<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    private function processNoInterface($response) {
        $return = [];
        $responses = $this->getResponseByName('ont.macAddr', $response);
        if($responses->error()) {
            throw new \Exception($responses->error());
        }
        foreach ($responses->fetchAll() as $r) {
            $onuNum = Helper::getIndexByOid($r->getOid());
            $ponNum = Helper::getIndexByOid($r->getOid(), 1);
            $interface = $this->parseInterface(($ponNum * 1000) + $onuNum);
            $return[] = [
                'interface' => $interface,
                'mac_address' => $r->getHexValue(),
            ];
        }
        return $return;
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($response) {
        $return = [];
        $responses = [];
        foreach ($response as $poolerResponse) {
            if($poolerResponse->error) continue;
            $responses[] = $poolerResponse->getResponse()[0];
        }
        foreach ($responses as $r) {
            $onuNum = Helper::getIndexByOid($r->getOid());
            $ponNum = Helper::getIndexByOid($r->getOid(), 1);
            $interface = $this->parseInterface(($ponNum * 1000) + $onuNum);
            $return[] = [
                'interface' => $interface,
                'mac_address' => $r->getHexValue(),
            ];
        }
        return $return;
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
        $oid = $this->oids->getOidByName('ont.macAddr');
        if(!$filter['interface']) {
            $this->response = $this->processNoInterface($this->formatResponse($this->snmp->walk([Oid::init($oid->getOid())])));
        } else {
            $oidId = $oid->getOid();
            $interface = $this->parseInterface($filter['interface']);
            $oids = Oid::init("{$oidId}.{$interface['_snmp_id']}");
            $this->response = $this->processWithInterface($this->snmp->get([$oids]));
        }

        return $this;
    }
}

