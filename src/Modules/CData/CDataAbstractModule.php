<?php

namespace SwitcherCore\Modules\CData;

use DI\DependencyException;
use DI\NotFoundException;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;

abstract class CDataAbstractModule extends AbstractModule
{
    private $interfaces;

    /**
     * @Inject
     * @var TelnetLazyConnect
     */
    protected $telnet;

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
        $this->setCache(json_encode($filter), $resp, 15);
        return $resp;
    }

    protected function parseInterface($input)
    {

        if (is_numeric($input) && $input < 100) {
            $interface = $this->findInterface($input, 'xid');
            if($interface === null) {
                throw new \Exception("Interface with xid=$input not found");
            }
            return [
                'name' => $interface['name'],
                'id' => $interface['id'],
                'xid' => $interface['xid'],
                'type' => $interface['type'],
                'onu_num' => null,
                'onu_id' => null,
                'uni' => null,
                'parent' => null,
                'pontype' => isset($interface['pontype']) ?  $interface['pontype'] : null,
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
                    'onu_num' => null,
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ?  $interface['pontype'] : null,
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
                    'type' => 'ONU',
                    'onu_num' => $onuNum,
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ?  $interface['pontype'] : null,
                ];
            }
        } elseif (!is_numeric($input)) {
            if (preg_match('/^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?\/?([0-9]{1,3})?$/', $input, $m)) {
                $interface = $this->findInterface("{$m[1]}{$m[2]}/{$m[3]}/{$m[4]}", 'name');
                $response = [
                    'name' => $interface['name'],
                    'id' => $interface['id'],
                    'parent' => null,
                    'xid' => $interface['xid'],
                    'type' => $interface['type'],
                    'onu_num' => null,
                    'uni' => null,
                    'pontype' => isset($interface['pontype']) ?  $interface['pontype'] : null,
                ];
                switch (count($m)) {
                    case 7:
                        $response['name'] .= ":{$m[5]}";
                        $response['uni'] = (int)$m[6];
                        $response['onu_num'] = (int)$m[5];
                        $response['type'] = 'UNI';
                        $response['id'] = (int)$m[5] + $response['id'];
                        break;
                    case 6:
                        $response['name'] .= ":{$m[5]}";
                        $response['onu_num'] = (int)$m[5];
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

    /**
     * @param $interface
     * @throws DependencyException
     * @throws NotFoundException
     */
    function getOntIdsByInterface($interface) {
        $interface = $this->parseInterface($interface);
        if($interface['type'] !== 'PON') {
            return [(int)$interface['id']];
        }
        $min = $interface['id'];
        $max = $min + 256;
        $ontIds = [];
        $onts = $this->getModule('pon_onts_status')->run()->getPretty();
        foreach ($onts as $ont) {
            if($ont['interface']['id'] > $min && $ont['interface']['id'] <= $max) {
                $ontIds[] = (int)$ont['interface']['id'];
            }
        }
        return  $ontIds;
    }

}
