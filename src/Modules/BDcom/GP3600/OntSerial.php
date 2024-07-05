<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Trap;
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

    public function trap(Trap $trap, $data)
    {
        if(!$data['interface']) {
            return null;
        }

        $filter = array_values(array_filter($data['parsed'], function ($item) {
            return $item['name'] == 'ont.serial';
        }));
        if(count($filter) === 0) {
            return  null;
        }

        return  [
            [
                'interface' => $data['interface'],
                'serial' => str_replace(":", "", $filter[0]['value']),
            ]
        ];
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {

        $DATA = [];
        $useCache = !isset($filter['use_cache']) || $filter['use_cache'] == 'yes';
        $resp = $this->getResponseByName('ont.serial');
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface'], 'id', !$useCache);
            $DATA[] = [
              'interface' => $iface,
              'serial' => str_replace(":", "", $resp->fetchOne()->getParsedValue()),
            ];
        }
        foreach ($resp->fetchAll() as $resp) {
            $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()), 'id', !$useCache);
            $DATA[] = [
                'interface' => $iface,
                'serial' => str_replace(":", "", $resp->getParsedValue()),
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

