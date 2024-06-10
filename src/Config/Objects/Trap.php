<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 05.06.2019
 * Time: 16:40
 */

namespace SwitcherCore\Config\Objects;


use InvalidArgumentException;

class Trap
{
    /**
     * @var string
     */
    public $name = "";
    /**
     * @var string
     */
    public $object = "";

    public $description = "";

    public $isInterface = false;

    public $modules = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function setObject(string $object): void
    {
        $this->object = $object;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isInterface(): bool
    {
        return $this->isInterface;
    }

    public function setIsInterface(bool $isInterface): void
    {
        $this->isInterface = $isInterface;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    protected function __construct()
    {
    }

    /**
     * @param array $array
     * @return Oid
     */
    static function init($array = []) : Trap
    {
        $oid = new Trap();
        if(isset($array['name']) && $array['name']) {
            $oid->setName($array['name']);
        } else {
            throw new InvalidArgumentException("Array for initialize oid must have 'name' element");
        }
        if(isset($array['object']) && $array['object']) {
            $oid->setObject($array['object']);
        } else {
            throw new InvalidArgumentException("Array for initialize oid must have 'object' element");
        }

        if(isset($array['description']) && $array['description']) {
            $oid->setDescription($array['description']);
        }
        $oid->setIsInterface(isset($array['isInterface']) && $array['isInterface']);
        $oid->setModules(isset($array['modules']) ? $array['modules'] : []);
        return $oid;
    }
}