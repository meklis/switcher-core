<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UnregisteredOnts extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
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
        $oids = array_map(function ($e) {
            return Oid::init($e->getOid());
        }, $this->oids->getOidsByRegex('ont.disabled\..*'));
        $response = $this->formatResponse($this->snmp->walk($oids));
        $this->response = array_values($this->getGponUnregisteredFromResponses($response));
        return $this;
    }

    function getGponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.disabled.ident'])) {
            if ($response['ont.disabled.ident']->error()) {
                return [];
            }
            foreach ($response['ont.disabled.ident']->fetchAll() as  $sn) {
                $portId = Helper::getIndexByOid($sn->getOid(), 1);
                $uniqId = Helper::getIndexByOid($sn->getOid());
                $ponPort = $this->parseInterface($portId, 'xid');
                $iface = $this->parseInterface($ponPort['name'] . ":" . $uniqId);
                $val = $sn->getValue();
                $hexVal = strtoupper(bin2hex(substr($val, 0, 4))) . substr($val, 5);
                $data["{$portId}.{$uniqId}"] = [
                    '_serial_ascii' => $sn->getValue(),
                    '_serial_hex' => $hexVal,
                    'serial' => str_replace(":", "", $sn->getValue()),
                    '_ident' => str_replace(":", "", $sn->getValue()),
                    'interface' => $iface,
                    'password' => null,
                    'version' => null,
                    'equipment_id' => null,
                    'fw_version' => null,
                    'check_code' => null,
                    'loid' => null,
                    'reg_time' => null,
                    'model' => null,
                    'type' => 'gpon',
                ];
            }
        }
        foreach ($response['ont.disabled.reason']->fetchAll() as $d) {
            $portId = Helper::getIndexByOid($d->getOid(), 1);
            $uniqId = Helper::getIndexByOid($d->getOid());
            if($d->getParsedValue() != 'UnknownSN') {
                unset($data["{$portId}.{$uniqId}"]);
            }
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }
}

