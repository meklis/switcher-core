<?php


namespace SwitcherCore\Switcher\Console;


use phpseclib3\Net\SSH2;

class SshLazyConnect implements ConsoleInterface
{
    protected $timeout = 60;
    protected $host_type = "";
    protected $prompt = '[>#$%]';
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $isConnected;
    protected $afterLoginCommands = [];
    protected $global_buffer;

    protected $buffer = '';

    /**
     * @var SSH2
     */
    protected $conn;

    function setHostType($hostType)
    {
        $this->host_type = $hostType;
        switch ($hostType) {
            case 'linux':  // General Linux/UNIX
                $this->setPrompt('\$');
                break;
            case 'ios':    // Cisco IOS, IOS-XE, IOS-XR
                $this->setPrompt('[>#]');
                break;
            case 'junos':  // Juniper Junos OS
                $this->setPrompt('[%>#]');
                break;
            case 'alaxala': // AlaxalA, HITACHI
                $this->setPrompt('[>#]');
                break;
            case 'dlink': // Dlink
                $this->setPrompt('[>|#]');
                break;
            case 'xos': // Xtreme routers and switches
                $this->setPrompt('\.[0-9]{1,3} > ');
                break;
            case 'bdcom': // BDcom PON switches
                $this->setPrompt('[ > ]');
                break;
            case 'cdata': // Cdata
                $this->setPrompt('OLT(.*?)[>#]');
                break;
        }
        return $this;
    }

    function __construct($host = '127.0.0.1', $port = 22, $timeout = 5 )
    {
        $this->timeout = $timeout;
        $this->host = $host;
        $this->port = $port;

    }
    public function getGlobalBuffer() {
        return $this->global_buffer;
    }

    function isConnected() {
        return $this->isConnected;
    }

    function connect()
    {
        $ssh = (new SSH2($this->host, $this->port, $this->timeout));
        $ssh->setWindowSize(512, 240);
        $ssh->disableSmartMFA();
        if (!$ssh->login($this->username, $this->password)) {
            throw new \Exception('Login failed');
        }
        $this->conn = $ssh;
        if(!$this->waitTo($this->prompt, true)) {
            throw new \Exception("Login failed");
        }
        foreach ($this->afterLoginCommands as $command) {
           $this->_exec($command);
        }
        $this->isConnected = true;
        return $this;
    }

    function disconnect()
    {
        $this->conn = null;
        $this->isConnected = false;
    }

    function setPrompt($prompt) {
        $this->prompt = $prompt;
        return $this;
    }

    function setAccess($username, $password, $host_type = "")
    {
        $this->username = $username;
        $this->password = $password;
        if($host_type) {
            $this->setHostType($host_type);
        }
        return $this;
    }

    function addCommandAfterLogin($command)
    {
        $this->afterLoginCommands[] = $command;
        return $this;
    }

    function exec($command, $add_newline = true, $prompt = null)
    {
        if(!$this->isConnected()) {
            $this->connect();
        }
        $resp = $this->_exec($command, $prompt);
        return trim($resp);
    }

    function _exec($command, $prompt = null) {
        if(!$prompt) {
            $prompt = $this->prompt;
        }
        $this->conn->write($command . "\n");;
        return $this->waitTo($prompt);
    }
    function waitTo($prompt, $strict = false) {
        $buffer = "";
        $found = false;
        while(true) {
            $line = $this->conn->read("", SSH2::READ_NEXT);
            if(!is_string($line)) break;
            $buffer .= $line;
            if(preg_match("/$prompt/", $line)) {
                $found  = true;
                break;
            }
        }
        $this->global_buffer .= $buffer;
        if($strict && !$found) return null;
        $lines = explode("\n", $buffer);
        return join("\n", array_slice($lines, 1, -1));
    }

    function __destruct()
    {
        try {
            if ($this->isConnected()) {
                switch ($this->host_type) {
                    case 'dlink':
                        $this->exec("logout");
                        break;
                    case 'ios':
                        $this->exec("exit");
                        break;
                    case 'cdata':
                        $this->exec("logout");
                        break;
                }
            }
        } catch (\Throwable $e) { }

    }
}
