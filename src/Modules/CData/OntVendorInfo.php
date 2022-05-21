<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends CDataAbstractModule
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
            unset($onts['status']);
            $return[$onts['interface']['id']] = $onts;
        }

        foreach ($this->getResponseByName('ont.verSoftware', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['ver_software'] = $r->getValue();
        }
        foreach ($this->getResponseByName('ont.verHardware', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['ver_hardware'] = $r->getValue();
        }
        foreach ($this->getResponseByName('ont.vendor', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['vendor'] = $r->getValue();
        }
        foreach ($this->getResponseByName('ont.model', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid());
            $return[$onuId]['model'] = $r->getValue();
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
                case 'ont.verSoftware': $return[$onuId]['ver_software'] = $wr->getValue(); break;
                case 'ont.verHardware': $return[$onuId]['ver_hardware'] = $wr->getValue(); break;
                case 'ont.vendor': $return[$onuId]['vendor'] = $wr->getValue(); break;
                case 'ont.model': $return[$onuId]['model'] = $wr->getValue(); break;
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
        $optical[] = $this->oids->getOidByName('ont.verSoftware');
        $optical[] = $this->oids->getOidByName('ont.verHardware');
        $optical[] = $this->oids->getOidByName('ont.vendor');
        $optical[] = $this->oids->getOidByName('ont.model');
        if(!$filter['interface']) {
            $oids = [];
            foreach ($optical as $opt) {
                $oids[] = Oid::init($opt->getOid());
            }
            $this->response = $this->processNoInterface($this->formatResponse($this->snmp->walk($oids)));
        } else {
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface']) as $id) {
                foreach ($optical as $optId) {
                   $oids[] = Oid::init("{$optId->getOid()}.$id");
                }
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids));
        }
        return $this;
    }
}

