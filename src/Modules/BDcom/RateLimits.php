<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class RateLimits extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getPretty()
    {
        $ifaces = [];
        if(isset($this->response['ont.bandwidthTxPIR'])) {
            $data = $this->getResponseByName('ont.bandwidthTxPIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['pir']['tx'] = $r->getValue();

                    if($r->getValue() == 1000000000) {
                        $ifaces[$xid]['limit']['tx'] = null;
                        $ifaces[$xid]['pretty']['tx'] = "∞";
                    } else {
                        $val = round($r->getValue() / 1000, 1);
                        $ifaces[$xid]['limit']['tx'] = $val;
                        if($val >= 1000) {
                            $ifaces[$xid]['pretty']['tx'] = round($val/1000, 1) . "G";
                        } else {
                            $ifaces[$xid]['pretty']['tx'] = $val . "M";
                        }
                    }
                }
            }
        }
        if(isset($this->response['ont.bandwidthRxPIR'])) {
            $data = $this->getResponseByName('ont.bandwidthRxPIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['pir']['rx'] = $r->getValue();

                    if($r->getValue() == 1000000000) {
                        $ifaces[$xid]['limit']['rx'] = null;
                        $ifaces[$xid]['pretty']['rx'] = "∞";
                    } else {
                        $val = round($r->getValue() / 1000, 1);
                        $ifaces[$xid]['limit']['rx'] = $val;
                        if($val >= 1000) {
                            $ifaces[$xid]['pretty']['rx'] = round($val/1000, 1) . "G";
                        } else {
                            $ifaces[$xid]['pretty']['rx'] = $val . "M";
                        }
                    }
                }
            }
        }
        if(isset($this->response['ont.bandwidthTxCIR'])) {
            $data = $this->getResponseByName('ont.bandwidthTxCIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['cir']['tx'] = $r->getValue();
                }
            }
        }
        if(isset($this->response['ont.bandwidthRxCIR'])) {
            $data = $this->getResponseByName('ont.bandwidthRxCIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['cir']['rx'] = $r->getValue();
                }
            }
        }
        if(isset($this->response['ont.bandwidthTxFIR'])) {
            $data = $this->getResponseByName('ont.bandwidthTxFIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['fir']['tx'] = $r->getValue();
                }
            }
        }
        if(isset($this->response['ont.bandwidthRxFIR'])) {
            $data = $this->getResponseByName('ont.bandwidthRxFIR');
            if(!$data->error()) {
                foreach ($data->fetchAll() as $r) {
                    $xid = Helper::getIndexByOid($r->getOid());
                    $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                    $ifaces[$xid]['fir']['rx'] = $r->getValue();
                }
            }
        }
        ksort($ifaces);
        return array_values(array_map(function ($e){
            if(!isset($e['limit']['rx'])) {
                $e['limit']['rx'] = null;
            }
            if(!isset($e['limit']['tx'])) {
                $e['limit']['tx'] = null;
            }

            if(!isset($e['pretty']['rx'])) {
                $e['pretty']['rx'] = 'N/A';
            }
            if(!isset($e['pretty']['tx'])) {
                $e['pretty']['tx'] = 'N/A';
            }

            if(!isset($e['cir'])) $e['cir'] = [
                'rx' => null,
                'tx' => null,
            ];
            if(!isset($e['pir'])) $e['pir'] = [
                'rx' => null,
                'tx' => null,
            ];
            if(!isset($e['fir'])) $e['fir'] = [
                'rx' => null,
                'tx' => null,
            ];
            return $e;
        },$ifaces));
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if(isset($filter['load_only'])) {
            $oids = [];
            $loadOnly = explode(",", $filter['load_only']);
            if(in_array("pir", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('ont.bandwidthTxPIR');
                $oids[] = $this->oids->getOidByName('ont.bandwidthRxPIR');
            }
            if(in_array("cir", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('ont.bandwidthTxCIR');
                $oids[] = $this->oids->getOidByName('ont.bandwidthRxCIR');
            }
            if(in_array("fir", $loadOnly)) {
                $oids[] = $this->oids->getOidByName('ont.bandwidthTxFIR');
                $oids[] = $this->oids->getOidByName('ont.bandwidthRxFIR');
            }
        } else {
           $oids = $this->oids->getOidsByRegex('ont\.bandwidth.*');
        }
        $oids = array_map(function ($e) {return $e->getOid();}, $oids);

        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) use ($iface) {
                return $e . "." . $iface['xid'];
            }, $oids);
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->get($oids));
        } else {
            $oids = array_map(function ($e) {return Oid::init($e); }, $oids);
            $this->response = $this->formatResponse($this->snmp->walk($oids));
        }
        return $this;
    }
}

