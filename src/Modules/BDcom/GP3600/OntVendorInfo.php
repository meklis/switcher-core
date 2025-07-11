<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntVendorInfo extends BDcomAbstractModule
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
        $data = $this->getResponseByName('ont.vendor');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['vendor'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.model');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['model'] = $this->convertHexToStringWithoutDelimiter($r->getValue(), true);
            }
        }
        $data = $this->getResponseByName('ont.modelId');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['model_id'] = $this->convertHexToString($r->getHexValue());
            }
        }
        $data = $this->getResponseByName('ont.omccVersion');
        if(!$data->error()) {
            foreach ($data->fetchAll() as $r) {
                $xid = Helper::getIndexByOid($r->getOid());
                $ifaces[$xid]['interface'] = $this->parseInterface($xid);
                $ifaces[$xid]['omcc_version'] = $this->convertHexToString($r->getHexValue());
            }
        }
        foreach ($this->response as $name => $value) {
            if(!preg_match('/^ont\.fwVer([0-9])(.*)$/', $name, $m)) continue;
            if($value->error()) continue;
            foreach ($value->fetchAll() as $val) {
                $xid = Helper::getIndexByOid($r->getOid());
                if(!isset($ifaces[$xid]['versions'][$m[1]])) {
                    $ifaces[$xid]['versions'][$m[1]] = [
                            'image_num' => (int)$m[1],
                            'version' => null,
                            'active' => null,
                            'valid' => null,
                            'committed' => null,
                    ];
                }
                $parsed = null;
                switch ($m[2]) {
                    case 'version':
                        $parsed = $this->convertHexToStringWithoutDelimiter($val->getValue(), true);
                        break;
                    case 'active':
                    case 'committed':
                    case 'valid':
                        $parsed = $val->getParsedValue() == 1;
                        break;
                }
                $ifaces[$xid]['versions'][$m[1]][$m[2]] = $parsed;
            }
        }
        ksort($ifaces);
        return array_values(array_map(function ($e){
            if(!isset($e['vendor'])) $e['vendor'] = null;
            if(!isset($e['model'])) $e['model'] = null;
            if(!isset($e['ver_software'])) $e['ver_software'] = null;
            if(!isset($e['ver_hardware'])) $e['ver_hardware'] = null;
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
        $vendorInfo[] = $this->oids->getOidByName('ont.vendor');
        $vendorInfo[] = $this->oids->getOidByName('ont.model');
        $vendorInfo[] = $this->oids->getOidByName('ont.modelId');
        $vendorInfo[] = $this->oids->getOidByName('ont.omccVersion');

        if($filter['interface']) {
            $vendorInfo = array_merge($vendorInfo, $this->oids->getOidsByRegex('^ont.fwVer'));
        }

        $oids = [];
        foreach ($vendorInfo as $oid) {
            $oids[] = $oid->getOid();
        }
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

    private function convertBytesToVersion($hex) {
        $symbols = explode(":", $hex);
        $str = '';
        foreach ($symbols as $symbol) {
            $str .= hexdec($symbol) . ".";
        }
        return trim($str, ".");
    }

}

