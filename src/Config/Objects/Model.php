<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 05.06.2019
 * Time: 16:37
 */

namespace SwitcherCore\Config\Objects;

use Exception;
use InvalidArgumentException;
use SwitcherCore\Modules\AbstractModule;

class Model
{
    /**
     * @var string
     */
    protected $name = "";
    /**
     * @var int
     */
    protected $ports = 0;
    /**
     * @var string
     */
    protected $deviceType = '';


    protected $rewrites = null;

    /**
     * @var Oid[]
     */
    protected $oids = [];

    /**
     * @var array
     */
    protected $detect = [];
    /**
     * @var array
     */
    protected $extra = [];

    /**
     * @var AbstractModule[]
     */
    protected $modules;


    /**
     * @var string
     */
    protected $key;

    /**
     * @var string[]
     */
    protected $modulesNames;


    /**
     * @var string[]
     */
    protected $inputs = [];


    function __set($name, $value) {
        $this->{$name} = $value;
    }


    public function getInputs()
    {
        return $this->inputs;
    }

    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getConsoleConnType()
    {
        return $this->getExtraParamByName('console_conn_type');
    }

    /**
     * @return string[]
     */
    function getModulesList()
    {
        $modules = [];
        foreach ($this->modulesNames as $name => $_) {
            $modules[] = $name;
        }
        return $modules;
    }

    /**
     * @return string[]
     */
    function getModulesListAssoc()
    {
        return $this->modulesNames;
    }

    public static function init($arr): Model
    {
        $model = new Model();
        if (isset($arr['name']) && $arr['name']) {
            $model->setName($arr['name']);
        } else {
            throw new InvalidArgumentException("Array for initialize oid must have 'name' element");
        }
        if (isset($arr['ports'])) {
            $model->setPorts($arr['ports']);
        } else {
            $model->setPorts(0);
        }
        if (isset($arr['key'])) {
            $model->setKey($arr['key']);
        } else {
            throw new InvalidArgumentException("Model with name '{$arr['name']}' must contain unique key");
        }
        if (isset($arr['detect']) && isset($arr['detect']['description'])) {
            $model->setDetect($arr['detect']);
        } else {
            throw new InvalidArgumentException("Array for initialize oid must have 'detect.description' element");
        }
        if (isset($arr['extra']) && is_array($arr['extra'])) {
            $model->setExtra($arr['extra']);
        }
        if (isset($arr['oids']) && is_array($arr['oids'])) {
            $model->setOidPatches($arr['oids']);
        }
        if (isset($arr['inputs'])) {
            $model->inputs = $arr['inputs'];
        }
        if (isset($arr['device_type'])) {
            $model->deviceType = $arr['device_type'];
        }

        if (isset($arr['modules'])) {
            $model->modulesNames = $arr['modules'];
        }

        if (isset($arr['rewrites'])) {
            $model->rewrites = $arr['rewrites'];
        }
        return $model;
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    public function getExtraParamByName($name = "")
    {
        if (isset($this->extra[$name])) {
            return $this->extra[$name];
        }
        throw new Exception("Extra param with name $name not found in model configuration");
    }

    public function detectByDescription($description)
    {
        if (!$description) return true;
        if (preg_match("/{$this->detect['description']}/", $description)) {
            return true;
        } else {
            return false;
        }
    }

    public function detectByHardWare($hardware)
    {
        if (!$hardware) return true;
        if (preg_match("/{$this->detect['hardware']}/", $hardware)) {
            return true;
        } else {
            return false;
        }
    }

    public function detectByObjId($objOid)
    {
        if (!$objOid) return true;
        if (preg_match("/{$this->detect['objid']}/", $objOid)) {
            return true;
        } else {
            return false;
        }
    }

    public function detectByIfacesCount($ifacesCount)
    {
        if (!$ifacesCount) return true;
        if (!isset($this->detect['ifacesCount'])) return true;
        return $ifacesCount == $this->detect['ifacesCount'];
    }

    public function getOidByName($name)
    {
        if (isset($this->oids[$name])) {
            return $this->oids[$name];
        } else {
            throw new InvalidArgumentException("Oid by name '$name' not found in model '{$this->name}'");
        }
    }

    public function getOidById($name)
    {
        if (isset($this->oids[$name])) {
            return $this->oids[$name];
        } else {
            throw new InvalidArgumentException("Oid by name '$name' not found in model '{$this->name}'");
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Model
     */
    public function setName(string $name): Model
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getPorts(): int
    {
        return $this->ports;
    }

    /**
     * @param int $ports
     * @return Model
     */
    public function setPorts(int $ports): Model
    {
        $this->ports = $ports;
        return $this;
    }

    /**
     * @return array
     */
    public function getOidsPatches(): array
    {
        return $this->oids;
    }

    /**
     * @param Oid[] $oids
     * @return Model
     */
    public function setOidPatches(array $oids): Model
    {
        $this->oids = $oids;
        return $this;
    }

    /**
     * @return array
     */
    public function getDetect(): array
    {
        return $this->detect;
    }

    /**
     * @param array $detect
     * @return Model
     */
    public function setDetect(array $detect): Model
    {
        $this->detect = $detect;
        return $this;
    }


    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     * @return Model
     */
    public function setExtra(array $extra): Model
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return Oid[]
     */
    public function getOids(): array
    {
        return $this->oids;
    }

    /**
     * @param Oid[] $oids
     * @return Model
     */
    public function setOids(array $oids): Model
    {
        $this->oids = $oids;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Model
     */
    public function setKey(string $key): Model
    {
        $this->key = $key;
        return $this;
    }


    protected function __construct()
    {
    }

    /**
     * @return null | string
     */
    public function getRewrites()
    {
        return $this->rewrites;
    }

    /**
     * @param null $rewrites
     */
    public function setRewrites($rewrites): void
    {
        $this->rewrites = $rewrites;
    }

    /**
     * @return AbstractModule[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param AbstractModule[] $modules
     */
    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    public function rewriteModelByValue($value)
    {
        if (!isset($this->getRewrites()['mapping'])) return $this;
        foreach ($this->getRewrites()['mapping'] as $rewrite) {
            if ($value != $rewrite['value']) continue;
            if (isset($rewrite['rewrite'])) {
                foreach ($rewrite['rewrite'] as $key => $value) {
                    $this->{$key} = $value;
                }
            }
        }
        return $this;
    }

}