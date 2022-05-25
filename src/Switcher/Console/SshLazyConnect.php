<?php


namespace SwitcherCore\Switcher\Console;

use Exception;
use Meklis\Network\Console\Helpers\HelperInterface;
use Meklis\Network\Console\SSH;

class SshLazyConnect extends SSH implements ConsoleInterface
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $isLogined = false;

    public function exec($command, $add_newline = true, $prompt = null)
    {
        if(!$this->isLogined) {
            $this->connect($this->host, $this->port);
            $this->login($this->username, $this->password);
            $this->isLogined = true;
        }
        return parent::exec($command, $add_newline, $prompt); // TODO: Change the autogenerated stub
    }

    function setAccess($username, $password)
    {
       $this->username = $username;
       $this->password = $password;
       return $this;
    }

    function setHost($host, $port = 23)
    {
       $this->host = $host;
       $this->port = $port;
       return $this;
    }


}
