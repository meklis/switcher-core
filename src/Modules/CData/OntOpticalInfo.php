<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfo extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    private function processNoInterface($response)
    {
        $return = [];
        foreach ($this->getModule('pon_onts_status')->run()->getPretty() as $iface) {
            $return[$iface['interface']['id']] = [
                'interface' => $iface['interface'],
                'rx' => null,
                'tx' => null,
                'voltage' => null,
                'temp' => null,
                'distance' => null,
            ];
        }

        if (!$this->getResponseByName('ont.opticalRx', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalRx', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 2);
                $interface = $this->parseInterface($onuId);
                $return[$onuId]['interface'] = $interface;
                $return[$onuId]['rx'] = round((float)$r->getValue() / 100, 2);
            }
        }
        if(!$this->getResponseByName('ont.opticalTx', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalTx', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 2);
                $return[$onuId]['tx'] = round((float)$r->getValue() / 100, 2);
            }
        }
        if(!$this->getResponseByName('ont.opticalVoltage', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalVoltage', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 2);
                $return[$onuId]['voltage'] = round((float)$r->getValue() / 100000, 2);
            }
        }
        if(!$this->getResponseByName('ont.opticalTemp', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalTemp', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 2);
                $return[$onuId]['temp'] = round((float)$r->getValue() / 100, 2);
            }
        }
        if(!$this->getResponseByName('ont.distance', $response)->error()) {
            foreach ($this->getResponseByName('ont.distance', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid());
                $return[$onuId]['distance'] = (int)$r->getValue();
            }
        }
        return array_values($return);
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function processWithInterface($response)
    {
        $return = [];
        $responses = [];
        foreach ($response as $poolerResponse) {
            if ($poolerResponse->error) continue;
            $responses[] = $poolerResponse->getResponse()[0];
        }
        foreach ($responses as $r) {
            $oid = $this->oids->findOidById($r->getOid());
            if ($oid->getName() === 'ont.distance') {
                $onuId = Helper::getIndexByOid($r->getOid());
            } else {
                $onuId = Helper::getIndexByOid($r->getOid(), 2);
            }
            $interface = $this->parseInterface($onuId);
            $return[$onuId]['interface'] = $interface;
            switch ($oid->getName()) {
                case 'ont.opticalRx':
                    $return[$onuId]['rx'] = round((float)$r->getValue() / 100, 2);
                    break;
                case 'ont.opticalTx':
                    $return[$onuId]['tx'] = round((float)$r->getValue() / 100, 2);
                    break;
                case 'ont.opticalVoltage':
                    $return[$onuId]['voltage'] = round((float)$r->getValue() / 100000, 2);
                    break;
                case 'ont.opticalTemp':
                    $return[$onuId]['temp'] = round((float)$r->getValue() / 100, 2);
                    break;
                case 'ont.distance':
                    $return[$onuId]['distance'] = (int)$r->getValue();
                    break;
            }
        }
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
        $optical = $this->oids->getOidsByRegex('ont.optical*');
        $optical[] = $this->oids->getOidByName('ont.distance');
        if (!$filter['interface']) {
            $oids = [];
            foreach ($optical as $opt) {
                $oids[] = Oid::init($opt->getOid());
            }
            $this->response = $this->processNoInterface($this->formatResponse($this->snmp->walk($oids)));
        } else {
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface']) as $id) {
                foreach ($optical as $optId) {
                    if ($optId->getName() === 'ont.distance') {
                        $oids[] = Oid::init("{$optId->getOid()}.$id");
                    } else {
                        $oids[] = Oid::init("{$optId->getOid()}.$id.0.0");
                    }
                }
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids));
        }
        return $this;
    }
}

