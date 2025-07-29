<?php


namespace SwitcherCore\Modules\Dlink\SfpDiag;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;


class SfpOpticalParser extends SwitchesPortAbstractModule
{
    protected $ports = [];
    protected $formatedResponse = [];
    function parse($filter = [])
    {
        return $this->formatedResponse;
    }


    function getPretty()
    {
        return $this->parse();
    }
    function getPrettyFiltered($filter = [])
    {
        return $this->parse($filter);
    }

    public function run($filter = [])
    {
        Helper::prepareFilter($params);
        $load_only = false;
        if($params['load_only']) $load_only = explode(',', $params['load_only']);
        $ports_list = $this->getPortList($filter);
        $results = [];
        foreach ($ports_list as $port=>$count_pairs) {
            $oids = [];
            if(!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.voltage')->getOid(). ".{$port}");
            if(!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.txPower')->getOid(). ".{$port}");
            if(!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.rxPower')->getOid(). ".{$port}");
            if(!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.temp')->getOid(). ".{$port}");
            $this->response = $this->formatResponse($this->snmp->get($oids));

            $result = [
                'interface' => $this->parseInterface($port),
                'temp' => null,
                'vcc' => null,
                'rx_power' => null,
                'tx_power' => null,
            ];
            try {
                $result['temp'] = $this->getResponseByName('sfp.ddm.temp')->fetchOne()->getParsedValue();
            } catch (\Throwable $t) {}
            try {
                $result['vcc'] = $this->getResponseByName('sfp.ddm.voltage')->fetchOne()->getParsedValue();
            } catch (\Throwable $t) {}
            try {
                $result['tx_power'] = $this->getResponseByName('sfp.ddm.rxPower')->fetchOne()->getParsedValue();
            } catch (\Throwable $t) {}
            try {
                $result['tx_power'] = $this->getResponseByName('sfp.ddm.rxPower')->fetchOne()->getParsedValue();
            } catch (\Throwable $t) {}
            $results[] = array_map(function ($e) {
                if($e === '-')  return null;
                return $e;
            }, $result);
        }
        $this->formatedResponse = $results;
        return $this;
    }
    protected function getPortList($filter) {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dlink.PortInfoMediumType')->getOid(), true),
        ]));
        $ports_list = [];
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($ports_list as $port=>$pairs) {
                if($interface['id'] != $port) {
                    unset($ports_list[$port]);
                }
            }
            return $ports_list;
        }
        foreach ($this->getResponseByName('dlink.PortInfoMediumType')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid());
            if($ident->getParsedValue() == 'Fiber') {
                 $ports_list[$port] = true;
            }
        }
        return $ports_list;
    }

}