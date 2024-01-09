<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class OntOpticalInfoFD12 extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    function getRaw()
    {
        return $this->response;
    }

    public function getOltRxFromConsole($interface, $fromCache = true)
    {
        $rx_arr = [];
        preg_match_all('!\d+!', $interface['name'], $matches);
        $cache_this_result_name = 'olr_rx_arr_' . $matches[0][0] . '/' . $matches[0][1] . '/' . $matches[0][2];

        if ($fromCache && $rx_arr = $this->getCache($cache_this_result_name, true)) {
            return $rx_arr;
        }

        $f_s = $matches[0][0] . '/' . $matches[0][1];
        $port_numb = $matches[0][2];
        $this->console->exec("interface {$interface['pontype']} {$f_s}");
        $resp = $this->console->exec("show port ddm-info {$port_numb} detail");
        $lines = explode("\n", $resp);
        for ($i = 0; $i < count($lines); $i++) {
            $parts = preg_split('/\s+/', trim($lines[$i]));
            if (count($parts) == 2) {
                $rx_arr[$parts[0]] = $parts[1];
            }
        }

        $this->setCache($cache_this_result_name, $rx_arr, 3600, true);
        return $rx_arr;
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

            if (!isset($return[$onuId]['olt_rx'])) {
                $rx_arr = $this->getOltRxFromConsole($interface);
                $rx = (isset($rx_arr[$interface['onu_num']])) ? $rx_arr[$interface['onu_num']] : null;
                $return[$onuId]['olt_rx'] = $rx;
            }

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
        if ($loadOnly === null || in_array('temp', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.opticalTemp');
        }
        if ($loadOnly === null || in_array('voltage', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.opticalVoltage');
        }
        if ($loadOnly === null || in_array('distance', $loadOnly)) {
            $optical[] = $this->oids->getOidByName('ont.distance');
        }
        if (!$filter['interface']) {
            $oids = [];
            foreach ($this->getAllOntsIds(true) as $id) {
                foreach ($optical as $optId) {
                    if ($optId->getName() === 'ont.distance') {
                        $oids[] = Oid::init("{$optId->getOid()}.$id");
                    } else {
                        $oids[] = Oid::init("{$optId->getOid()}.$id.0.0");
                    }
                }
            }
            $this->response = $this->process($this->snmp->get($oids));
        } else {
            $oids = [];
            foreach ($this->getOntIdsByInterface($filter['interface'], true) as $id) {
                foreach ($optical as $optId) {
                    if ($optId->getName() === 'ont.distance') {
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

