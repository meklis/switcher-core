<?php


namespace SwitcherCore\Modules\CData\FD11XX;


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
            $return[$iface['interface']['_snmp_id']] = [
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
                $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                if(!isset($return[$onuId])) continue;
                $return[$onuId]['rx'] = (int)$r->getValue() ? round(10 * log10($r->getValue()) - 40, 2) : null;
            }
        }
        if (!$this->getResponseByName('ont.opticalTx', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalTx', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                if(!isset($return[$onuId])) continue;
                $return[$onuId]['tx'] = (int)$r->getValue() ? round(10 * log10($r->getValue()) - 40, 2) : null;
            }
        }
        if (!$this->getResponseByName('ont.opticalVoltage', $response)->error()) {
            foreach ($this->getResponseByName('ont.opticalVoltage', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                if(!isset($return[$onuId])) continue;
                $return[$onuId]['voltage'] = round((float)$r->getValue() / 10000, 2);
            }
        }
        if (!$this->getResponseByName('ont.distance', $response)->error()) {
            foreach ($this->getResponseByName('ont.distance', $response)->fetchAll() as $r) {
                $onuId = Helper::getIndexByOid($r->getOid(), 1) . "." . Helper::getIndexByOid($r->getOid());
                if(!isset($return[$onuId])) continue;
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
    private function processWithInterface($response, $iface = null)
    {
        $return = [];
        $responses = [];
        foreach ($response as $poolerResponse) {
            if ($poolerResponse->error) continue;
            $responses[] = $poolerResponse->getResponse()[0];
        }
        foreach ($responses as $r) {
            $oid = $this->oids->findOidById($r->getOid());
            $return['interface'] = $iface;
            switch ($oid->getName()) {
                case 'ont.opticalRx':
                    $return['rx'] = round((float)$r->getValue() / 100, 2);
                    break;
                case 'ont.opticalTx':
                    $return['tx'] = round((float)$r->getValue() / 100, 2);
                    break;
                case 'ont.opticalVoltage':
                    $return['voltage'] = round((float)$r->getValue() / 100000, 2);
                    break;
                case 'ont.distance':
                    $return['distance'] = (int)$r->getValue();
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
            $iface = $this->parseInterface($filter['interface']);
            foreach ($optical as $optId) {
                    $oids[] = Oid::init("{$optId->getOid()}.{$iface['_snmp_id']}");
            }
            $this->response = $this->processWithInterface($this->snmp->get($oids), $iface);
        }
        return $this;
    }
}

