<?php


namespace SwitcherCore\Modules\Snmp\ZTE;


use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

abstract class ZteAbstractModule extends AbstractModule
{
    private $indexes = [];
    function getIndexes($ethernetOnly = true)
    {
        if(count($this->indexes) > 0) {
            return $this->indexes;
        }
        $cStatus = $this->obj->walker->getCacheStatus();
        $response = $this->obj->walker->useCache(true)->walk(
            [$this->obj->oidCollector->getOidByName('if.Name')->getOid()]
        );
        $this->obj->walker->useCache($cStatus);
        foreach ($response as $resp) {
            foreach ($resp->response as $in) {
                $resp = explode("_", $in->getValue());
                $this->indexes[Helper::getIndexByOid($in->getOid())] = $resp[1];
            }
        }
        return $this->indexes;
    }

    function getPortByIndex($index) {
        if(isset($this->indexes[$index])) return $this->indexes[$index];
        return Helper::ztePonIndexDecode($index);
    }
    function getIndexByPort($port, $type='eonu') {
        if(in_array($port, $this->getIndexes())) {
            foreach ($this->getIndexes() as $index=>$name) {
                if($name == $port) {
                    return $index;
                }
            }
        }

        if(strpos($port, ":") !== false &&
            preg_match('/([0-9]{1,3})\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,4})/', $port, $match)) {
            return Helper::ztePonIndexEncode($type, $match[1], $match[2], $match[3], $match[4]);
        } elseif (preg_match('/([0-9]{1,3})\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,4})/', $port, $match)) {
            return Helper::ztePonIndexEncode('slot', $match[1], $match[2], $match[3]);
        } else {
            throw  new \Exception("Error encode PON port to number");
        }
    }
}