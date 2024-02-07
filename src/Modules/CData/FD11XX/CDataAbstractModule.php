<?php

namespace SwitcherCore\Modules\CData\FD11XX;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class CDataAbstractModule extends AbstractModule
{
    private $interfaces;

    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    function getPretty()
    {
        return [];
    }

    function __construct(Model $model) {
        $this->interfaces = $model->getExtraParamByName('interfaces');
    }

    function getInterfacesIds()
    {
        $data = [];
        foreach ($this->model->getExtraParamByName('interfaces') as $iface) {
            $data[$iface['id']] = $iface;
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if($fromCache && $ret = $this->getCache(json_encode($filter))) {
            return $ret;
        }
        $resp = $this->getPretty();
        $this->setCache(json_encode($filter), $resp, 15);
        return $resp;
    }

    protected function parseInterface($input, $parseBy = 'xid')
    {
        if (is_numeric($input) && $input < 100) {
            $interface = $this->findInterface($input, $parseBy);
            if($interface === null) {
                throw new \Exception("Interface with {$parseBy}=$input not found");
            }
            return [
                'name' => $interface['name'],
                'id' => $interface['id'],
                'xid' => $interface['xid'],
                'type' => $interface['type'],
                'onu_num' => null,
                'parent' => null,
                '_snmp_id' => null,
            ];
        } elseif (is_numeric($input) && $input > 1000) {
            //Check is port
            $ponPortId = floor($input / 1000);
            $onuNum = $input - ($ponPortId * 1000);
            if ($interface = $this->findInterface($ponPortId, 'xid')) {
                return [
                    'name' => $interface['name'] . ":" . $onuNum,
                    'parent' => $interface['id'],
                    'id' => ($interface['xid'] * 1000) + $onuNum,
                    'xid' => null,
                    'type' => 'ONU',
                    'onu_num' => $onuNum,
                    '_snmp_id' => "{$interface['xid']}.{$onuNum}",
                ];
            }
        } elseif (!is_numeric($input)) {
            if (preg_match('/^pon1\/([0-9])\:?([0-9]{1,3})?$/', $input, $m)) {
                $interface = $this->findInterface("pon1/{$m[1]}", 'name');
                $response = [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'parent' => null,
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    'onu_num' => null,
                    '_snmp_id' => null,
                ];
                switch (count($m)) {
                    case 3:
                        $response['name'] .= ":{$m[2]}";
                        $response['parent'] = $interface['id'];
                        $response['onu_num'] = (int)$m[2];
                        $response['type'] = 'ONU';
                        $response['_snmp_id'] = "{$response['xid']}.{$m[2]}";
                        $response['id'] = ((int)$response['xid'] * 1000) + $m[2];
                        break;
                }
                return $response;
            }
        }
       throw new \InvalidArgumentException("Error parse interface by ident='{$input}'");
    }

    private function findInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->interfaces, function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return $filtered[0];
        }
        return null;
    }

    protected function _exe($command) {
        $resp = $this->console->exec($command);
        if(strpos($resp, "Unknown command") !== false) {
            throw new \Exception($resp);
        }
        return true;
    }
}
