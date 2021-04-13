<?php

namespace SwitcherCore\Modules\CData;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;

abstract class CDataAbstractModule extends AbstractModule
{
    private $interfaces;


    function getPretty()
    {
        return [];
    }

    function __construct(Model $model) {
        $this->interfaces = $model->getExtraParamByName('interfaces');
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if($fromCache && $ret = $this->getCache(json_encode($filter))) {
            return $ret;
        }
        $resp = $this->getPretty();
        if(isset($filter['meta']) && $filter['meta'] !== 'yes') {
            foreach ($resp as $k=>$_) {
                unset($resp[$k]['_interface']);
            }
        }
        $this->setCache(json_encode($filter), $resp, 10);
        return $resp;
    }

    protected function parseInterface($input)
    {

        if (is_numeric($input) && $input < 100) {
            $interface = $this->findInterface($input, 'xid');
            return [
                'name' => $interface['name'],
                'id' => $interface['id'],
                'xid' => $interface['xid'],
                'type' => $interface['type'],
                'onu_num' => null,
                'onu_id' => null,
                'uni' => null,
            ];
        } elseif (is_numeric($input) && $input > 10000) {
            //Check is port
            if ($interface = $this->findInterface($input, 'id')) {
                return [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    'onu_num' => null,
                    'onu_id' => null,
                    'uni' => null,
                ];
            }
            //Find ont number
            $arr = str_split(dechex($input));
            $xid = hexdec("{$arr[3]}{$arr[4]}");
            $onuNum = hexdec("{$arr[5]}{$arr[6]}");
            if ($interface = $this->findInterface($xid, 'xid')) {
                return [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    'onu_num' => $onuNum,
                    'onu_id' => $interface['id'] + $onuNum,
                    'uni' => null,
                ];
            }
        } elseif (!is_numeric($input)) {
            if (preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $input, $m)) {
                $interface = $this->findInterface("{$m[1]}{$m[2]}/{$m[3]}/{$m[4]}", 'name');
                $response = [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    'onu_num' => null,
                    'onu_id' => null,
                    'uni' => null,
                ];
                switch (count($m)) {
                    case 7:
                        $response['uni'] = (int)$m[6];
                        $response['onu_num'] = (int)$m[5];
                        $response['type'] = 'UNI';
                        $response['onu_id'] = (int)$m[5] + $response['id'];
                        break;
                    case 6:
                        $response['onu_num'] = (int)$m[5];
                        $response['type'] = 'ONU';
                        $response['onu_id'] = (int)$m[5] + $response['id'];
                        break;
                }
                return $response;
            }
        }
       return null;
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

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getOntIdsByInterface($interface) {
        $interface = $this->parseInterface($interface);
        if($interface['onu_num']) {
            return [(int)$interface['onu_id']];
        }
        if($interface['type'] !== 'PON') {
            return [(int)$interface['id']];
        }
        $min = $interface['id'];
        $max = $min + 256;
        $ontIds = [];
        $onts = $this->getModule('pon_onts_status')->run()->getPretty();
        foreach ($onts as $ont) {
            if($ont['_id'] > $min && $ont['_id'] <= $max) {
                $ontIds[] = (int)$ont['_id'];
            }
        }
        return  $ontIds;
    }
}