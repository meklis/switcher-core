<?php


namespace SwitcherCore\Switcher;

/**
 * Class Device
 * @property int $snmpRepeats
 * @property int $snmpTimeoutSec
 * @property int $mikrotikApiPort
 * @property int $consolePort
 * @property int $consoleTimeout
 * @property int $snmpPort
 * @property string $consoleConnectionType
 *
 * @package SwitcherCore\Switcher
 */

class Device
{

    const CONSOLE_SSH = 'ssh';
    const CONSOLE_TELNET = 'telnet';

    protected $ip;
    protected $community;
    protected $login;
    protected $password;
    protected $modelKey;
    protected $checkAlive = true;
    protected $meta = [];

    function __construct()
    {
    }

    function __get($name)
    {
        return isset($this->meta[$name]) ? $this->meta[$name] : null;
    }
    function __set($key, $value) {
        $this->meta[$key] = $value;
    }
    function set($key, $value) {
        $this->__set($key, $value);
        return $this;
    }
    function get($key) {
        return $this->__get($key);
    }

    /**
     * @return bool
     */
    public function isCheckAlive(): bool
    {
        return $this->checkAlive;
    }

    /**
     * @param bool $checkAlive
     * @return Device
     */
    public function setCheckAlive(bool $checkAlive): Device
    {
        $this->checkAlive = $checkAlive;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     * @return Device
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @param mixed $community
     * @return Device
     */
    public function setCommunity($community)
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     * @return Device
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Device
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     * @return Device
     */
    public function setMeta(array $meta): Device
    {
        $this->meta = $meta;
        return $this;
    }

    public static function init($ip, $community, $login, $password) {
        return (new self())
            ->setIp($ip)
            ->setCommunity($community)
            ->setLogin($login)
            ->setPassword($password);
    }

    public function getObject() {
        return $this;
    }

    /**
     * @return null | string
     */
    public function getModelKey()
    {
        return $this->modelKey;
    }

    /**
     * @param mixed $modelKey
     */
    public function setModelKey($modelKey)
    {
        $this->modelKey = $modelKey;
        return $this;
    }

}
