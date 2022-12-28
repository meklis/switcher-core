<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntSerial extends BDcomAbstractModule
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

        $resp = $this->getResponseByName('ont.serial');
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            return [
              'interface' => $iface,
              'serial' => $resp->fetchOne()->getParsedValue(),
            ];
        }
        $DATA = [];
        foreach ($resp->fetchAll() as $resp) {
            $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
            $DATA[] = [
                'interface' => $iface,
                'serial' => $resp->getParsedValue(),
            ];
        }
        return  $this->sortResponseByInterface($DATA);
    }



    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, [
                $this->oids->getOidByName('ont.serial')->getOid() . ".{$iface['xid']}"
            ]);
            $this->response = $this->formatResponse(
                $this->snmp->get($oids)
            );
        } else {
            $oids = array_map(function ($e) {
                return \SnmpWrapper\Oid::init($e);
            }, [
                $this->oids->getOidByName('ont.serial')->getOid()
            ]);
            $this->response = $this->formatResponse(
                $this->snmp->walk($oids)
            );
        }

        return $this;
    }
}

