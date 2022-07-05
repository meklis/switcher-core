<?php


namespace SwitcherCore\Modules\CData\FD11XX;


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
            $return[$onts['interface']['_snmp_id']] = $onts;
        }

        foreach ($this->getResponseByName('ont.verSoftware', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
            if(!isset($return[$onuId])) continue;
            $return[$onuId]['ver_software'] = $r->getValue();
            $return[$onuId]['vendor'] = null;
            $return[$onuId]['model'] = null;
        }
        foreach ($this->getResponseByName('ont.verHardware', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
            if(!isset($return[$onuId])) continue;
            $return[$onuId]['ver_hardware'] = $r->getValue();
        }
        foreach ($this->getResponseByName('ont.serial', $response)->fetchAll() as $r) {
            $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
            if(!isset($return[$onuId])) continue;
            $return[$onuId]['serial'] = $this->convertHexToString($r->getHexValue());
        }
        return array_values($return);
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($response, $interface) {
        $return = [];
        $responses = [];
        foreach ($this->getModule('pon_onts_status')->run()->getPrettyFiltered(['meta' => 'yes']) as $onts) {
            $return[$onts['interface']['id']] = $onts;
        }
        foreach ($response as $poolerResponse) {
            if($poolerResponse->error) continue;
            $oid = $this->oids->findOidById($poolerResponse->getOid());
            $responses[] = WrappedResponse::init($poolerResponse, $oid->getValues());
        }
        foreach ($responses as $r) {
            $wr = $r->fetchAll()[0];
            $oid = $this->oids->findOidById($wr->getOid());
            switch ($oid->getName()) {
                case 'ont.verSoftware': $return['ver_software'] = $wr->getValue(); break;
                case 'ont.verHardware': $return['ver_hardware'] = $wr->getValue(); break;
                case 'ont.serial': $return['serial'] = $this->convertHexToString($wr->getHexValue()); break;
            }
        }
        $return['interface'] = $interface;
        $return['model']  = null;
        $return['vendor'] = null;
        return array_values([$return]);
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
        $optical[] = $this->oids->getOidByName('ont.serial');
        if(!$filter['interface']) {
            $oids = [];
            foreach ($optical as $opt) {
                $oids[] = Oid::init($opt->getOid());
            }
            $this->response = $this->processNoInterface($this->formatResponse($this->snmp->walk($oids)));
        } else {
            $oids = [];
            $interface = $this->parseInterface($filter['interface']);
            foreach ($optical as $optId) {
               $oids[] = Oid::init("{$optId->getOid()}.{$interface['_snmp_id']}");
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids), $interface);
        }
        return $this;
    }
}

