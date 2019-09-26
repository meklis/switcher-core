<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 05.06.2019
 * Time: 16:40
 */

namespace Switcher\Config\Objects;


class Oid
{
    const ACCESS_READ = "read";
    const ACCESS_WRITE = "write";
    const TYPE_OCTETSTRING = "string";
    const TYPE_INTEGER = "integer";
    /**
     * @var string
     */
    public $name = "";
    /**
     * @var string
     */
    public $oid = "";
    /**
     * @var array
     */
    public $values = [];
    /**
     * @var Oid::ACCESS_READ|Oid::ACCESS_WRITE
     */
    public $access = Oid::ACCESS_READ;
    /**
     * @var Oid::TYPE_OCTETSTRING|Oid::TYPE_INTEGER
     */
    public $type = Oid::TYPE_INTEGER;


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Oid
     */
    public function setName(string $name): Oid
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOid(): string
    {
        return $this->oid;
    }

    /**
     * @param string $oid
     * @return Oid
     */
    public function setOid(string $oid): Oid
    {
        $this->oid = $oid;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param  Oid::ACCESS_READ|Oid::ACCESS_WRITE $access
     * @return Oid
     */
    public function setAccess(string $access): Oid
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     * @return Oid
     */
    public function setValues(array $values): Oid
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Oid::TYPE_INTEGER|Oid::TYPE_OCTETSTRING $type
     * @return Oid
     */
    public function setType(string $type): Oid
    {
        $this->type = $type;
        return $this;
    }

    protected function __construct()
    {
    }

    /**
     * @param array $array
     * @return Oid
     */
    static function init($array = []) : Oid
    {
        $oid = new Oid();
        if(isset($array['name']) && $array['name']) {
            $oid->setName($array['name']);
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'name' element");
        }
        if(isset($array['oid']) && $array['oid']) {
            $oid->setOid($array['oid']);
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'oid' element");
        }
        if(isset($array['access'])) {
            if($array['access'] === 'read') {
                $oid->setAccess(Oid::ACCESS_READ);
            } elseif ($array['access'] === 'write') {
                $oid->setAccess(Oid::ACCESS_WRITE);
            } else {
                throw new \InvalidArgumentException("Array for initialize oid has incorrect value for 'access'. Must be 'read' or 'write'");
            }
        } else {
            throw new \InvalidArgumentException("Array for initialize oid must have 'access' element");
        }

        if(isset($array['type'])) {
            if($array['type'] === 'string') {
                $oid->setType(Oid::TYPE_OCTETSTRING);
            } elseif ($array['type'] === 'integer') {
                $oid->setType(Oid::TYPE_INTEGER);
            } else {
                throw new \InvalidArgumentException("Array for initialize oid has incorrect value for 'type'. Must be 'string' or 'integer'");
            }
        }

        if(isset($array['values'])) {
            if(!is_array($array['values'])) {
                throw new \InvalidArgumentException("Values must be as array(hash map, key=>value)");
            }
            $oid->setValues($array['values']);
        }
        if(isset($array['values'])) {
            if(!is_array($array['values'])) {
                throw new \InvalidArgumentException("Values must be as array(hash map, key=>value)");
            }
            $oid->setValues($array['values']);
        }


        return $oid;
    }
}