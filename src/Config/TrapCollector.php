<?php



namespace SwitcherCore\Config;


use ErrorException;
use Exception;
use SwitcherCore\Config\Objects\Model;
use SwitcherCore\Config\Objects\Trap;


class TrapCollector extends Collector
{
    /**
     * @var Trap[]
     */
    protected $cacheNames = [];
    /**
     * @var Trap[]
     */
    protected $cacheIds = [];
    protected function read()
    {
        $this->updateOidCache($this->reader->readGlobalTraps());
    }

    /**
     * @param Trap[] $traps
     * @return $this
     */
    protected function updateOidCache($traps) {
        foreach ($traps as $trap) {
            $this->cacheIds[$trap->getObject()] = $trap;
            $this->cacheNames[$trap->getName()] = $trap;
        }
        return $this;
    }

    /**
     * @param Trap $trap
     */
    public function addTrap2Cache($trap) {
        $this->cacheIds[$trap->getObject()] = $trap;
        $this->cacheNames[$trap->getName()] = $trap;
        return $this;
    }


    /**
     * @param Model $model
     * @return $this
     * @throws ErrorException
     */
    function readEnterpriceTraps(Model $model) {
        $this->updateOidCache($this->reader->readEnterpriseTraps($model));
        return $this;
    }

    /**
     * @return array|Trap[]
     */
    function getTraps() {
        return array_values($this->cacheNames);
    }

    /**
     * @param $oidName
     * @return Trap
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
     * @return Trap[]
     */
    function getTrapsByRegex($regex) {
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
     * @return Trap
     * @throws Exception
     */
    function getTrapById($oidId) {
        if(isset($this->cacheIds[$oidId])) {
            return $this->cacheIds[$oidId];
        } else {
            throw new Exception("Oid with id '$oidId' not found");
        }
    }
    function findTrapById($oidId) {
        foreach ($this->cacheIds as $oid) {
            if(strpos($oidId, $oid->getObject()) !== false) {
                $stack = explode(".", $oidId);
                $needle = explode(".", $oid->getObject());
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
        throw new Exception("Object with id $oidId not found");
    }

}