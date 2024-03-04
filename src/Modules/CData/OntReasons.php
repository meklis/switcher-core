<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReasons extends CDataAbstractModule
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
        foreach ($this->getModule('pon_onts_status')->run()->getPrettyFiltered(['meta' => 'yes']) as $onts) {
            $return[$onts['interface']['id']]['interface'] = $onts['interface'];
        }

        foreach ($this->getResponseByName('ont.lastRegSince', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid(),0);
            $return[$onuId]['last_reg'] = date("Y-m-d H:i:s", time() - $r->getValue());
            $return[$onuId]['last_reg_since'] = $r->getValueAsTimeTicks(SnmpResponse::HUMANIZE_DURATION);
        }
        foreach ($this->getResponseByName('ont.lastDownReason', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['last_down_reason'] = $r->getParsedValue();
        }
        return array_values($return);
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($response) {
        $return = [];
        $responses = [];
        foreach ($this->getModule('pon_onts_status')->run()->getPrettyFiltered(['meta' => 'yes']) as $onts) {
            $return[$onts['interface']['id']] = $onts;
        }
        $issetIds = [];
        foreach ($response as $poolerResponse) {
            if($poolerResponse->error) continue;
            $oid = $this->oids->findOidById($poolerResponse->getOid());
            $responses[] = WrappedResponse::init($poolerResponse, $oid->getValues());
        }
        $regTimeSeconds = null;
        $onlineTimeSeconds = null;
        foreach ($responses as $r) {
            $wr = $r->fetchAll()[0];
            $oid = $this->oids->findOidById($wr->getOid());
            $onuId = Helper::getIndexByOid($wr->getOid());
            $issetIds[$onuId] = true;
            switch ($oid->getName()) {
                case 'ont.lastRegSince': $return[$onuId]['last_reg'] =  date("Y-m-d H:i:s", time() - $wr->getValue());
                                         $return[$onuId]['last_reg_since'] = $wr->getValueAsTimeTicks(SnmpResponse::HUMANIZE_DURATION);
                                         $regTimeSeconds = ['sec' => $wr->getValue(), 'onu_id' => $onuId];
                                         break;
                case 'ont.lastDownSince': $onlineTimeSeconds = ['sec' => $wr->getValue(), 'onu_id' => $onuId];
                                         break;
                case 'ont.lastDownReason': $return[$onuId]['last_down_reason'] = $wr->getParsedValue(); break;
            }
        }
        if($onlineTimeSeconds && $regTimeSeconds && $return[$onlineTimeSeconds['onu_id']]['status'] !== 'Online') {
            $return[$onlineTimeSeconds['onu_id']]['last_dereg'] = date("Y-m-d H:i:s", time() - ($regTimeSeconds['sec'] - $onlineTimeSeconds['sec']));
        }

        $ids = array_keys($issetIds);
        $return = array_filter($return, function ($e) use ($ids) {

          return in_array($e['interface']['id'], $ids);
        });
        return array_values($return);
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
        $optical[] = $this->oids->getOidByName('ont.lastRegSince');
        $optical[] = $this->oids->getOidByName('ont.lastDownSince');
        $optical[] = $this->oids->getOidByName('ont.lastDownReason');

        if(!$filter['interface']) {
            $oids = [];
            foreach ($optical as $opt) {
                $oids[] = Oid::init($opt->getOid());
            }
            $this->response = $this->processNoInterface($this->formatResponse($this->snmp->walk($oids)));
        } else {
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface']) as $id) {
                foreach ($optical as $oid) {
                    $oids[] = Oid::init("{$oid->getOid()}.$id");
                }
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids));
        }
        return $this;
    }
}

