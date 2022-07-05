<?php


namespace SwitcherCore\Modules\CData\FD11XX;


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
            $return[$onts['interface']['id']] = $onts;
        }

        foreach ($this->getResponseByName('ont.lastRegSince', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid(),0);
            $return[$onuId]['last_reg'] = time() - $r->getValue();
            $return[$onuId]['last_reg_since'] = $r->getValueAsTimeTicks(SnmpResponse::HUMANIZE_DURATION);
        }
        foreach ($this->getResponseByName('ont.lastDownReason', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['last_down_reason'] = $r->getParsedValue();
        }
        foreach ($this->getResponseByName('ont.adminStatus', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['admin_status'] = $r->getParsedValue();
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
        foreach ($responses as $r) {
            $wr = $r->fetchAll()[0];
            $oid = $this->oids->findOidById($wr->getOid());
            $onuId = Helper::getIndexByOid($wr->getOid());
            $issetIds[$onuId] = true;
            switch ($oid->getName()) {
                case 'ont.lastRegSince': $return[$onuId]['last_reg'] = time() - $wr->getValue();
                                         $return[$onuId]['last_reg_since'] = $wr->getValueAsTimeTicks(SnmpResponse::HUMANIZE_DURATION);
                                         break;
                case 'ont.lastDownReason': $return[$onuId]['last_down_reason'] = $wr->getParsedValue(); break;
                case 'ont.adminStatus': $return[$onuId]['admin_status'] = $wr->getParsedValue(); break;
            }
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
        $optical[] = $this->oids->getOidByName('ont.lastDownReason');
        $optical[] = $this->oids->getOidByName('ont.adminStatus');

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

