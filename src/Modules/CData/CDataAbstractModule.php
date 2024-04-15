<?php

namespace SwitcherCore\Modules\CData;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

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

    function __construct(Model $model)
    {
        $this->interfaces = $model->getExtraParamByName('interfaces');
    }

    function getInterfacesIds()
    {
        $data = [];
        foreach ($this->model->getExtraParamByName('interfaces') as $iface) {
            $data[$iface['xid']] = $iface;
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if ($fromCache && $ret = $this->getCache(json_encode($filter))) {
            return $ret;
        }
        $resp = $this->getPretty();
        $this->setCache(json_encode($filter), $resp, 15);
        return $resp;
    }

    protected function parseInterface($input)
    {

        if (is_numeric($input) && $input < 100) {
            $interface = $this->findInterface($input, 'xid');
            if ($interface === null) {
                throw new \Exception("Interface with xid=$input not found");
            }
            return [
                'name' => $interface['name'],
                'id' => $interface['id'],
                'xid' => $interface['xid'],
                'type' => $interface['type'],
                '_port' => isset($interface['_port'])  ? $interface['_port'] : null,
                '_slot' => isset($interface['_slot']) ? $interface['_slot'] : null,
                '_onu_num' => null,
                'onu_id' => null,
                'uni' => null,
                'parent' => null,
                'pontype' => isset($interface['pontype']) ? $interface['pontype'] : null,
            ];
        } elseif (is_numeric($input) && $input > 10000) {
            //Check is port
            if ($interface = $this->findInterface($input, 'id')) {
                return [
                    'name' => $interface['name'],
                    'parent' => null,
                    'id' => $interface['id'],
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    '_port' => isset($interface['_port'])  ? $interface['_port'] : null,
                    '_slot' => isset($interface['_slot']) ? $interface['_slot'] : null,
                    '_onu_num' => null,
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ? $interface['pontype'] : null,
                ];
            }
            //Find ont number
            $arr = str_split(dechex($input));
            $xid = hexdec("{$arr[3]}{$arr[4]}");
            $onuNum = hexdec("{$arr[5]}{$arr[6]}");
            if ($interface = $this->findInterface($xid, 'xid')) {
                return [
                    'name' => $interface['name'] . ":{$onuNum}",
                    'parent' => $interface['id'],
                    'id' => $interface['id'] + $onuNum,
                    'xid' => $interface['xid'],
                    '_port' => isset($interface['_port'])  ? $interface['_port'] : null,
                    '_slot' => isset($interface['_slot']) ? $interface['_slot'] : null,
                    '_onu_num' => $onuNum,
                    'type' => 'ONU',
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ? $interface['pontype'] : null,
                ];
            }
        } elseif (!is_numeric($input)) {
            if (preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]{1,3})\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $input, $m)) {
                $interface = $this->findInterface("{$m[1]}{$m[2]}/{$m[3]}/{$m[4]}", 'name');
                $response = [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'parent' => null,
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    '_port' => isset($interface['_port'])  ? $interface['_port'] : null,
                    '_slot' => isset($interface['_slot']) ? $interface['_slot'] : null,
                    '_onu_num' => null,
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ? $interface['pontype'] : null,
                ];
                switch (count($m)) {
                    case 7:
                        $response['name'] .= ":{$m[5]}";
                        $response['uni'] = (int)$m[6];
                        $response['_onu_num'] = (int)$m[5];
                        $response['type'] = 'UNI';
                        $response['id'] = (int)$m[5] + $response['id'];
                        break;
                    case 6:
                        $response['name'] .= ":{$m[5]}";
                        $response['_onu_num'] = (int)$m[5];
                        $response['type'] = 'ONU';
                        $response['id'] = (int)$m[5] + $response['id'];
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

    protected $_ontStatuses = null ;
    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getOntIdsByInterface($interface, $onlyOnline = false)
    {
        $interface = $this->parseInterface($interface);
        if ($interface['type'] !== 'PON') {
            return [(int)$interface['id']];
        }
        $min = $interface['id'];
        $max = $min + 256;
        if(!$this->_ontStatuses) {
            $onts = $this->getModule('pon_onts_status')->run()->getPretty();
            $this->_ontStatuses = $onts;
        } else {
            $onts = $this->_ontStatuses;
        }

        $ontIds = [];
        foreach ($onts as $ont) {
            if ($ont['interface']['id'] > $min && $ont['interface']['id'] <= $max) {
                if ($onlyOnline && $ont['status'] !== 'Online') {
                    continue;
                }
                $ontIds[] = (int)$ont['interface']['id'];
            }
        }
        return $ontIds;
    }

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getAllOntsIds($onlyOnline = false)
    {
        $onts = $this->getModule('pon_onts_status')->run()->getPretty();
        foreach ($onts as $ont) {
            if ($onlyOnline && $ont['status'] !== 'Online') {
                continue;
            }
            $ontIds[] = (int)$ont['interface']['id'];
        }
        return $ontIds;
    }

    protected function _exe($command)
    {
        $resp = $this->console->exec($command);
        if (strpos($resp, "Unknown command") !== false) {
            throw new \Exception($resp);
        }
        return true;
    }
}
