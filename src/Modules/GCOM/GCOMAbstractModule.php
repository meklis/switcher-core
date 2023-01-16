<?php

namespace SwitcherCore\Modules\GCOM;

use DI\DependencyException;
use DI\NotFoundException;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class GCOMAbstractModule extends AbstractModule
{
    /**
     * @Inject
     * @var \SwitcherCore\Switcher\Console\ConsoleInterface
     */
    protected $console;

    private $physicalInterfaces = [];

    function getPretty()
    {
        return [];
    }

    function __construct(Model $model) {
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $resp = $this->getPretty();
        return $resp;
    }

    protected function parseInterface($input)
    {
        if(is_string($input) && preg_match('/^p?([0-9])\/([0-9]{1,2})[:\/]([0-9]{1,3})$/', $input, $m)) {
            $id = $this->getIdByName("{$m[1]}/{$m[2]}/{$m[3]}");
            return  [
               'id' => $id,
               'parent' => ((int) ($id / 1000) * 1000),
               'name' => "{$m[1]}/{$m[2]}:{$m[3]}",
               'xid' =>  "{$m[1]}.{$m[2]}.{$m[3]}",
               'type' => 'ONU',
            ];
        }
        if(is_string($input) && preg_match('/([0-9])\.([0-9]{1,2})\.([0-9]{1,3})$/', $input, $m)) {
            $id = $this->getIdByName("{$m[1]}/{$m[2]}/{$m[3]}");
            return  [
               'id' => $id,
               'parent' => ((int) ($id / 100) * 100),
               'name' => "{$m[1]}/{$m[2]}:{$m[3]}",
               'xid' =>  "{$m[1]}.{$m[2]}.{$m[3]}",
               'type' => 'ONU',
            ];
        }
        if(is_string($input) && preg_match('/([0-9]{1,2})\.([0-9]{1,3})$/', $input, $m)) {
            $id = $this->getIdByName("{$m[1]}/{$m[2]}");
            return  [
               'id' => $id,
               'parent' => null,
               'name' => "{$m[1]}/{$m[2]}",
               'xid' =>  "{$m[1]}.{$m[2]}",
               'type' => 'PON',
            ];
        }

        if(is_numeric($input) && $input > 1000000) {
            $input = (string)$input;
            $slot = (int)$input[1];
            $port = (int)($input[2] . $input[3]);
            $onuNum = (int)($input[4] . $input[5] . $input[6]);
            if($onuNum) {
                return  [
                    'id' => $this->getIdByName("{$slot}/{$port}:{$onuNum}"),
                    'parent' => 1000000 + ($slot * 100000) + ($port * 1000),
                    'name' => "{$slot}/{$port}:{$onuNum}",
                    'xid' =>  "{$slot}.{$port}.{$onuNum}",
                    'type' => 'ONU',
                ];
            } else {
                return  [
                    'id' => $this->getIdByName("{$slot}/{$port}"),
                    'parent' => null,
                    'name' => "p{$slot}/{$port}",
                    'xid' =>  "{$slot}.{$port}",
                    'type' => 'PON',
                ];
            }
        }

        foreach ($this->getPhysicalInterfaces() as $physicalInterface) {
            $ifaces[$physicalInterface['id']] = $physicalInterface;
        }
        if(is_numeric($input) && isset($ifaces[$input])) {
            return $ifaces[$input];
        }
        if (is_numeric($input)) {
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == $iface['xid'];
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }
        if (is_string($input)) {
            $input = strtolower($input);
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == strtolower($iface['name']);
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }
        if (is_string($input)) {
            $input = strtolower($input);
            $filtered = array_values(array_filter($ifaces, function ($iface) use ($input) {
                return $input == strtolower($iface['name']);
            }));
            if(count($filtered) > 0) {
                return  $filtered[0];
            }
        }

        throw new \InvalidArgumentException("Error find interface by ident='{$input}'");
    }
    protected function getPhysicalInterfaces() {
        if(!$this->physicalInterfaces) {
            $this->loadInterfaces();
        }
        return $this->physicalInterfaces;
    }
    private function loadInterfaces() {
        $data = $this->getCache("interfaces_list", true);
        if($data) {
            $this->physicalInterfaces = $data['ifaces_physical'];
            return $this;
        }
        $data = $this->formatResponse($this->snmp->walk([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Descr')->getOid()),
        ]));

        //Build physical interfaces list
        $this->physicalInterfaces = [];
        foreach ($this->getResponseByName('if.Descr', $data)->fetchAll() as $iface) {
            $xid = Helper::getIndexByOid($iface->getOid());
            $id = $this->getIdByName($iface->getValue());
            if (preg_match('/^e([0-9]\/[0-9]{1,3})$/', $iface->getValue(), $m)) {
                $this->physicalInterfaces[] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $iface->getValue(),
                    'type' => 'GE',
                ];
            } else if (preg_match('/^p([0-9]\/[0-9]{1,3})$/', strtolower($iface->getValue()), $m)) {
                $this->physicalInterfaces[] = [
                    'id' => $id,
                    'xid' => $xid,
                    'name' => $iface->getValue(),
                    'type' => 'PON',
                ];
            }
        }

        sort($this->physicalInterfaces);
        $this->setCache("interfaces_list", [
            'ifaces_physical' => $this->physicalInterfaces,
        ], 300, true);
        return $this;
    }

    private function findParentByName($name) {
        //EPON0/2:48
        if(preg_match('/^([0-9]{1,2})\/([0-9]{1,2}):([0-9]{1,3})$/', strtolower($name), $matches)) {
            $id =  1000000;
            $id += 10000 *  $matches[1] ;
            $id += 1000 * $matches[2];
            return $id;
        }
        if(preg_match('/^p([0-9]{1,2})\/([0-9]{1,2})/', strtolower($name), $matches)) {
            $id =  1000000;
            $id += 10000 *  $matches[1] ;
            $id += 1000 * $matches[2];
            return $id;
        }
        return  null;
    }
    private function getIdByName($name) {
        //EPON0/2:48
        if(preg_match('/^p?([0-9]{1,2})\/([0-9]{1,2})[:\/]([0-9]{1,3})$/', strtolower($name), $matches)) {
            $id =  1000000;
            $id += 10000 *  $matches[1] ;
            $id += 1000 * $matches[2];
            $id +=  $matches[3];
            return $id;
        }
        if(preg_match('/^p?([0-9]{1,2})\/([0-9]{1,2})$/', strtolower($name), $matches)) {
            $id =  1000000;
            $id += 10000 *  $matches[1] ;
            $id += 1000 * $matches[2];
            return $id;
        }
        if(preg_match('/^e([0-9])\/([0-9]{1,2})$/', $name, $matches)) {
            $id = 1000;
            $id += 100 *  $matches[1] ;
            $id += $matches[2];
            return $id;
        }
        return null;
    }
    private function findPhysicalInterface($findValue, $findKey)
    {
        $filtered = array_values(array_filter($this->physicalInterfaces, function ($val) use ($findValue, $findKey) {
            return isset($val[$findKey]) && $val[$findKey] == $findValue;
        }));
        if (count($filtered) > 0) {
            return $filtered[0];
        }
        return null;
    }

    protected function sortResponseByInterface($response) {
        $RESP = [];
        foreach ($response as $resp) {
            if(isset($resp['interface']['id'])) {
                $RESP[$resp['interface']['id']] = $resp;
            } else if(isset($resp['interface'])) {
                $RESP[$resp['id']] = $resp;
            } else {
                throw new \Exception("Incorrect array for sorting by interface");
            }
        }
        ksort($RESP);
        return array_values($RESP);
    }

    /**
     * @param PoollerResponse[] $responses
     * @return void
     */
    protected function checkSnmpRespError($responses) {
        foreach ($responses as $response) {
            if($response->error) {
                throw new \SNMPException($response->error);
            }
        }
        return;
    }

    function getOnuXidByOid($oid) {
        return
            Helper::getIndexByOid($oid, 2) . "." .
            Helper::getIndexByOid($oid, 1) . "." .
            Helper::getIndexByOid($oid);
    }
}
