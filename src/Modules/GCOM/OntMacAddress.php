<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntMacAddress extends GCOMAbstractModule
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
            $onuId = Helper::getIndexByOid($r->getOid());
            $interface = $this->parseInterface($onuId);
            $interface['onu_id'] = $onuId;
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
            $onuId = Helper::getIndexByOid($r->getOid());
            $interface = $this->parseInterface($onuId);
            $interface['onu_id'] = $onuId;
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
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface']) as $id) {
                $oids[] = Oid::init("{$oidId}.$id");
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids));
        }

        return $this;
    }
}

