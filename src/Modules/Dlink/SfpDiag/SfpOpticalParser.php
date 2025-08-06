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
        Helper::prepareFilter($filter);
        $load_only = false;
        if ($filter['load_only']) $load_only = explode(',', $filter['load_only']);
        $ports_list = $this->getPortList($filter);
        $results = [];
        $oids = [];
        foreach ($ports_list as $port => $interface) {
            if (!$load_only || in_array('vcc', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.voltage')->getOid() . ".{$port}");
            if (!$load_only || in_array('tx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.txPower')->getOid() . ".{$port}");
            if (!$load_only || in_array('rx_power', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.rxPower')->getOid() . ".{$port}");
            if (!$load_only || in_array('temp', $load_only)) $oids[] = Oid::init($this->oids->getOidByName('sfp.ddm.temp')->getOid() . ".{$port}");
            $results[$port] = [
                'interface' => $interface,
                'temp' => null,
                'vcc' => null,
                'rx_power' => null,
                'tx_power' => null,
                'tx_bias' => null,
            ];
        }
        $this->response = $this->formatResponse($this->snmp->get($oids));
        $this->_fillResponse($results, 'temp', 'sfp.ddm.temp');
        $this->_fillResponse($results, 'vcc', 'sfp.ddm.voltage');
        $this->_fillResponse($results, 'tx_power', 'sfp.ddm.txPower');
        $this->_fillResponse($results, 'rx_power', 'sfp.ddm.rxPower');


        $this->formatedResponse = array_values($results);
        return $this;
    }

    protected function _fillResponse(&$results, $keyName, $oidName)
    {
        try {
            $data = $this->getResponseByName($oidName)->fetchAll();
            if ($data) {
                foreach ($data as $d) {
                    $idx = Helper::getIndexByOid($d->getOid());
                    $value = $d->getValue();
                    if(trim($value) == '-') {
                        $value = null;
                    } else {
                        $value = round($value, 2);
                    }
                    $results[$idx][$keyName] = $value;
                }
            }
        } catch (\Throwable $t) {}
    }

    protected function getPortList($filter)
    {
        $this->response = $this->formatResponse($this->snmp->walk([
            Oid::init($this->oids->getOidByName('dlink.PortInfoMediumType')->getOid(), true),
        ]));
        $ports_list = [];
        foreach ($this->getResponseByName('dlink.PortInfoMediumType')->fetchAll() as $ident) {
            $port = Helper::getIndexByOid($ident->getOid(), 1);
            if ($ident->getParsedValue() == 'Fiber') {
                $ports_list[$port] = $this->parseInterface($port);
            }
        }
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($ports_list as $port => $pairs) {
                if ($interface['id'] != $port) {
                    unset($ports_list[$port]);
                }
            }
        }
        return $ports_list;
    }

}
