<?php


namespace SwitcherCore\Config\Objects;



class CommandLine
{
    public $command;
    public $preparedCommand;
    public $waitFor;
    public $fail;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        if(!$this->preparedCommand) {
            throw new \Exception("You must prepare line with arguments before get");
        }
        return $this->preparedCommand;
    }
    public function resetPrepare() {
        $this->preparedCommand = "";
        return $this;
    }

    /**
     * @param mixed $command
     * @return CommandLine
     */
    protected function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWaitFor()
    {
        return $this->waitFor;
    }

    /**
     * @param mixed $waitFor
     * @return CommandLine
     */
    protected function setWaitFor($waitFor)
    {
        $this->waitFor = $waitFor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isFailAllow()
    {
        return $this->fail;
    }

    /**
     * @param mixed $fail
     * @return CommandLine
     */
    protected function setFail($fail)
    {
        $this->fail = $fail;
        return $this;
    }

    protected function __construct()
    {
    }

    public function prepare($arr = []) {
        $line = $this->command;
        foreach ($arr as $name=>$value) {
             $line = str_replace("\{\{$name\}\}", $value, $line);
        }
        $this->preparedCommand = $line;
        return $this;
    }
    function getArgumentsList() {
        $list = [];
        if(preg_match_all('/\{(.*?)\}/', $this->command, $matching)) {
            foreach ($matching as $match) {
                $list[] = $match[1];
            }
        }
        return $list;
    }
    /**
     * @param $arr
     * @return CommandLine
     */
    static public function init($arr) {
        $line = new CommandLine();
        return $line->setCommand($arr['line'])->setFail($arr['fail'])->setWaitFor($arr['wait_for']);
    }
}