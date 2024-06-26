<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GponOntSerial extends CDataAbstractModuleFD16xxV3
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
        $responses = $this->getResponseByName('ont.serialNum', $response);
        if($responses->error()) {
            throw new \Exception($responses->error());
        }
        foreach ($responses->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $interface = $this->parseInterface($onuId);
            $interface['onu_id'] = $onuId;
            if(!$interface) {
                continue;
            }
            $return[] = [
                'interface' => $interface,
                'serial' => Helper::hexToStr(substr($r->getHexValue(), 0, 12)) . substr(str_replace(":", "", $r->getHexValue()), 8),
                '_raw' => $r->getHexValue(),
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
                'serial' => Helper::hexToStr(substr($r->getHexValue(), 0, 12)) . substr(str_replace(":", "", $r->getHexValue()), 8),
                '_raw' => $r->getHexValue(),
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
        $oid = $this->oids->getOidByName('ont.serialNum');
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

