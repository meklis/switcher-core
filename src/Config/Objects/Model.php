<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 05.06.2019
 * Time: 16:37
 */

namespace SwitcherCore\Config\Objects;

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
     * @var Oid[]
     */
    protected $oids  = [];

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
     * @var string[]
     */
    protected $modulesNames;


    /**
     * @var string[]
     */
    protected $inputs = [];


    public function getInputs() {
        return $this->inputs;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTelnetConnType() {
       return $this->getExtraParamByName('telnet_conn_type');
    }

    /**
     * @return string[]
     */
    function getModulesList() {
        $modules = [];
        foreach ($this->modulesNames as $name=>$_) {
            $modules[] = $name;
        }
        return $modules;
    }
    /**
     * @return string[]
     */
    function getModulesListAssoc() {
        return $this->modulesNames;
    }

    public static  function init($arr) : Model {
        $model = new Model();
        if(isset($arr['name']) && $arr['name']) {
            $model->setName($arr['name']);
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'name' element");
        }
        if(isset($arr['ports'])) {
            $model->setPorts($arr['ports']);
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'ports' element");
        }
        if(isset($arr['detect']) && isset($arr['detect']['description'])) {
            $model->setDetect($arr['detect']);
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'detect.description' element");
        }
        if(isset($arr['extra']) && is_array($arr['extra'])) {
            $model->setExtra($arr['extra']);
        }
        if(isset($arr['oids']) && is_array($arr['oids'])) {
            $model->setOidPatches($arr['oids']);
        }
        if(isset($arr['inputs'])) {
            $model->inputs = $arr['inputs'];
        }

        if(isset($arr['modules'])) {
            $model->modulesNames = $arr['modules'];
        }
        return $model;
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    public function getExtraParamByName($name = "") {
        if(isset($this->extra[$name])) {
            return $this->extra[$name];
        }
        throw new \Exception("Extra param with name $name not found in model configuration");
    }
    public function detectByDescription($description) {
        if(!$description) return true;
        if(preg_match("/{$this->detect['description']}/", $description)) {
            return true;
        } else {
            return false;
        }
    }
    public function detectByHardWare($hardware) {
        if(!$hardware) return true;
        if(preg_match("/{$this->detect['hardware']}/", $hardware)) {
            return true;
        } else {
            return false;
        }
    }
    public function detectByObjId($objOid) {
        if(!$objOid) return true;
        if(preg_match("/{$this->detect['objid']}/", $objOid)) {
            return true;
        } else {
            return false;
        }
    }

    public function getOidByName($name) {
        if(isset($this->oids[$name])) {
            return $this->oids[$name];
        } else {
            throw new \InvalidArgumentException("Oid by name '$name' not found in model '{$this->name}'");
        }
    }
    public function getOidById($name) {
        if(isset($this->oids[$name])) {
            return $this->oids[$name];
        } else {
            throw new \InvalidArgumentException("Oid by name '$name' not found in model '{$this->name}'");
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


    protected function __construct(){}

}