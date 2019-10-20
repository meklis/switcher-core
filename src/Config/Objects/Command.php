<?php


namespace Config\Objects;


use SwitcherCore\Config\Objects\CommandLine;
class Command
{
    /**
     * @var string
     */
    public $name = "";
    /**
     * @var CommandLine[]
     */
    protected $lines = [];
    protected $counter = 0;

    public $ignoreFail = false;

    /**
     * @param $arr
     * @return Command
     */
    public static  function init($arr) {
        $command = new Command();
        $command->name = $arr['name'];
        $command->ignoreFail = $arr['ignore_fail'];
        foreach ($arr['lines'] as $line) {
            $command->lines = CommandLine::init($line);
        }
        return $command;
    }


    protected function __construct(){}

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return CommandLine|bool
     * @throws \Exception
     */
    public function fetchLine() {
        if(count($this->lines) == 0) {
            throw new \Exception("You must prepare commands before fatching");
        }
        if(isset($this->lines[$this->counter])) {
            return  $this->lines[$this->counter++];
        }
        return false;
    }

    /**
     * @return CommandLine[]
     */
    public function fetchAll() {
        return $this->lines;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    function prepare($arguments = []) {
        foreach ($this->lines as $line) {
            $line->prepare($arguments);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function reset() {
        $this->counter = 0;
        foreach ($this->lines as $line) {
            $line->resetPrepare();
        }
        return $this;
    }
}