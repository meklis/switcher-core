<?php


namespace SwitcherCore\Switcher\Objects;

use Exception;
use \meklis\network\Telnet;

class TelnetLazyConnect extends Telnet
{
    protected $is_logined;
    protected $proxy_addr = "";
    protected $timeout = 60;
    protected $username = "";
    protected $password = "";
    protected $host_type = "";
    protected $stream_timeout_sec = 5;
    protected $afterLoginCommands =[];
    function setHostType($host_type) {
        $this->host_type = $host_type;
        return $this;
    }

    function __construct($host = '127.0.0.1', $port = 23, $timeout = 10, $stream_timeout = 1.0)
    {
        if(!$timeout) {
            $timeout = $this->timeout;
        }
        if(!$stream_timeout) {
            $stream_timeout = $this->stream_timeout_sec;
        }
        parent::__construct($host, $port, $timeout, $stream_timeout);
    }

    function login($username, $password, $host_type="")
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }
    function addCommandAfterLogin($command) {
        $this->afterLoginCommands[] = $command;
        return $this;
    }
    function exec($command, $add_newline = true, $prompt = null)
    {
        if(!$this->is_logined) {
            parent::setLinuxEOL();
            parent::disableMagicControl();
            try {
                parent::setWindowSize(840,500);
            } catch (\Exception $e) {

            }
            parent::login($this->username, $this->password, $this->host_type);
            $this->setStreamTimeout(10.0);
            switch ($this->host_type) {
                case "dlink":
                    parent::exec("disa clip");
                    break;
            }
            if($this->afterLoginCommands) {
                foreach ($this->afterLoginCommands as $comm) {
                     mb_convert_encoding(parent::exec($comm, true, $prompt), 'UTF-8', 'UTF-8');
                }
            }
        }
        $this->is_logined = true;
        return  mb_convert_encoding(parent::exec($command, $add_newline), 'UTF-8', 'UTF-8'); // TODO: Change the autogenerated stub
    }
    function __destruct()
    {
        try {
            if($this->is_logined) {
                parent::setStreamTimeout(0.1);
                switch ($this->host_type) {
                    case 'dlink':
                        parent::exec("logout");
                        break;
                    case 'ios':
                        parent::exec("exit");
                        break;
                }
            }
        } catch (Exception $e) {}
        parent::__destruct(); // TODO: Change the autogenerated stub
    }
}
