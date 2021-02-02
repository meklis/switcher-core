<?php

namespace SwitcherCore\Modules\CData;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Modules\AbstractModule;

abstract class CDataAbstractModule extends AbstractModule
{
    protected $interfaces;


    protected function parseInterface($input)
    {
        $this->interfaces = $this->model->getExtraParamByName('interfaces');

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
                    'onu_id' => null,
                    'uni' => null,
                ];
            }
        } elseif (!is_numeric($input)) {
            if (preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $input, $m)) {
                $interface = $this->findInterface("{$m[0]}{$m[1]}/{$m[2]}/{$m[3]}");
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
                        $response['onu_id'] = (int)$m[5] + $response['xid'];
                        break;
                    case 6:
                        $response['onu_num'] = (int)$m[5];
                        $response['type'] = 'ONU';
                        $response['onu_id'] = (int)$m[5] + $response['xid'];
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
        $response = $this->getModule('pon_onts_status')->run()->getPretty();
        print_r($response);
    }
}