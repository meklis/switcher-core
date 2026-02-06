<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntOpticalInfoFD16 extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     * @throws \SwitcherCore\Exceptions\IncompleteResponseException
     */
    private function process($response)
    {
        $return = [];
        $responses = [];
        foreach ($response as $poolerResponse) {
            if(isset($poolerResponse->getResponse()[0])) {
                $responses[] = $poolerResponse->getResponse()[0];
            }
        }
        foreach ($responses as $r) {
            $oid = $this->oids->findOidById($r->getOid());
            if ($oid->getName() === 'ont.distance' || $oid->getName() === 'pon.portOpticalRxOfOnu') {
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
                case 'pon.portOpticalRxOfOnu':
                    $return[$onuId]['olt_rx'] = round((float)$r->getValue() / 100, 2);
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
        return array_values(array_map(function ($e) {
            if(!isset($e['voltage'])) $e['voltage'] = null;
            if(!isset($e['temp'])) $e['temp'] = null;
            if(!isset($e['distance'])) $e['distance'] = null;
            if(!isset($e['tx'])) $e['tx'] = null;
            if(!isset($e['olt_rx'])) $e['olt_rx'] = null;
            if(!isset($e['rx'])) $e['rx'] = null;
            if($e['rx'] == -0.01) $e['rx'] = null;
            if($e['olt_rx'] == -0.01) $e['rx'] = null;
            if($e['rx'] < -100) $e['rx'] = null;
            if($e['olt_rx'] < -100) $e['rx'] = null;
            if($e['tx'] < -100) $e['rx'] = null;
            return $e;
        },$return));
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
        $loadOnly = null;
        if ($filter['load_only']) {
            $loadOnly = explode(",", $filter['load_only']);
        }
        $optical = [];
        if ($loadOnly === null || in_array('tx', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.opticalTx');
        }
        if ($loadOnly === null || in_array('rx', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.opticalRx');
        }
        if ($loadOnly === null || in_array('olt_rx', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('pon.portOpticalRxOfOnu');
        }
        if ($loadOnly === null || in_array('temp', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.opticalTemp');
        }
        if ($loadOnly === null || in_array('distance', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.distance');
        }
        if (!$filter['interface']) {
            $oids = [];
            foreach ($this->getAllOntsIds(true) as $id) {
                foreach ($optical as $optId) {
                    if ($optId->getName() === 'ont.distance' || $optId->getName() === 'pon.portOpticalRxOfOnu') {
                        $oids[] = Oid::init("{$optId->getOid()}.$id");
                    } else {
                        $oids[] = Oid::init("{$optId->getOid()}.$id.0.0");
                    }
                }
            }

            $responses = $this->snmp->get($oids);
            $this->response = $this->process($responses);
        } else {
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface'], true) as $id) {
                foreach ($optical as $optId) {
                    if ($optId->getName() === 'ont.distance' || $optId->getName() === 'pon.portOpticalRxOfOnu') {
                        $oids[] = Oid::init("{$optId->getOid()}.$id");
                    } else {
                        $oids[] = Oid::init("{$optId->getOid()}.$id.0.0");
                    }
                }
            }
            $this->response = $this->process($this->snmp->get($oids));
        }
        return $this;
    }
}

