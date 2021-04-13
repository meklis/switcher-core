<?php



namespace SwitcherCore\Config;


use ErrorException;
use Exception;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Oid;


class OidCollector extends Collector
{
    /**
     * @var Oid[]
     */
    protected $cacheNames = [];
    /**
     * @var Oid[]
     */
    protected $cacheIds = [];
    protected function read()
    {
        $this->updateOidCache($this->reader->readGlobalOids());
    }

    /**
     * @param Oid[] $oids
     * @return $this
     */
    protected function updateOidCache($oids) {
        foreach ($oids as $oid) {
            $this->cacheIds[$oid->getOid()] = $oid;
            $this->cacheNames[$oid->getName()] = $oid;
        }
        return $this;
    }

    /**
     * @param Oid $oid
     */
    public function addOid2Cache($oid) {
        $this->cacheIds[$oid->getOid()] = $oid;
        $this->cacheNames[$oid->getName()] = $oid;
        return $this;
    }


    /**
     * @param Model $model
     * @return $this
     * @throws ErrorException
     */
    function readEnterpriceOids(Model $model) {
        $this->updateOidCache($this->reader->readEnterpriseOids($model));
        return $this;
    }

    /**
     * @return array|Oid[]
     */
    function getOids() {
        return array_values($this->cacheNames);
    }

    /**
     * @param $oidName
     * @return Oid
     * @throws Exception
     */
    function getOidByName($oidName) {
        if(isset($this->cacheNames[$oidName])) {
            return $this->cacheNames[$oidName];
        } else {
            throw new Exception("Oid with name '$oidName' not found");
        }
    }

    /**
     * @param $regex
     * @return Oid[]
     */
    function getOidsByRegex($regex) {
        $oids = [];
        foreach ($this->cacheNames as $name=>$oid) {
            if(preg_match("/{$regex}/", $name)) {
                $oids[] = $oid;
            }
        }
        return $oids;
    }

    /**
     * @param $oidId
     * @return Oid
     * @throws Exception
     */
    function getOidById($oidId) {
        if(isset($this->cacheIds[$oidId])) {
            return $this->cacheIds[$oidId];
        } else {
            throw new Exception("Oid with id '$oidId' not found");
        }
    }

    /**
     * @param $oidId
     * @param bool $reverse
     * @return Oid
     * @throws Exception
     *//*
    function getOidByRegexId($oidId, $reverse = true) {

        foreach ($this->cacheNames as $oid) {
            $o = $oid->getOid();
            if($reverse) {
                if (preg_match("/^{$o}/", $oidId)) {
                    return $oid;
                }
            } else {
                if (preg_match("/^{$oidId}/", $o)) {
                    return $oid;
                }
            }
        }
        throw new \Exception("Oid with id $oidId not found");
    }*/
    function findOidById($oidId) {
        foreach ($this->cacheNames as $oid) {
            if(strpos($oidId, $oid->getOid()) !== false) {
                $stack = explode(".", $oidId);
                $needle = explode(".", $oid->getOid());
                $sstack = "";
                $sneedle = "";
                foreach ($needle as $num=>$_) {
                    $sneedle .= '.' . $stack[$num];
                    $sstack .= '.' . $needle[$num];
                }
                if($sstack == $sneedle) {
                    return $oid;
                }
            }
        }
        throw new Exception("Oid with id $oidId not found");
    }

}