<?php

namespace SwitcherCore\Dev;

use Monolog\Logger;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\PhpCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends Command
{

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $question;

    function interact(InputInterface $input, OutputInterface $output)
    {
        $this->prepareForQuestions($input, $output);
        parent::interact($input, $output); // TODO: Change the autogenerated stub
    }

    abstract function exec(InputInterface $input, OutputInterface $output);
    function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->exec($input,$output);
        return 0;
    }

    function toYaml($data) {
        if(is_array($data)) {
            return yaml_emit(json_decode(json_encode($data, JSON_NUMERIC_CHECK), true));
        }
        if(is_string($data)) {
            return  yaml_emit(json_decode($data, true));
        }
        throw new \Exception("Unknown data format");
    }
    function toJson($data) {
        if(is_array($data) || is_object($data)) {
            return json_encode($data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        }
        return $data;
    }

    function prepareForQuestions(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->question = $this->getHelper('question');
        return $this;
    }

    function question($question, $default = '', $validCallback = null, $autocompleteValues = [])
    {
        $question = (new Question("{$question}: ", $default));
        if ($validCallback) {
            $question->setValidator($validCallback)->setMaxAttempts(5);
        }
        if($autocompleteValues) {
            $question->setAutocompleterValues($autocompleteValues);
        }
        return $this->question->ask($this->input, $this->output, $question);
    }
    function choiseQuestion($question, $values = [], $default = 0, $multiSelect = false)
    {
        $question = (new ChoiceQuestion(
            $question,
            $values,
            $default
        ))->setMaxAttempts(5);
        $question->setMultiselect($multiSelect);
        return $this->question->ask($this->input, $this->output, $question);
    }

    function confirm($question, $default = false)
    {
        $question = trim($question);
        $suffix = " (y/n, default: " . ($default ? 'yes' : 'no') . ")? ";
        $question = (new ConfirmationQuestion($question . $suffix, $default));
        return $this->question->ask($this->input, $this->output, $question);
    }

    /**
     * @return \SwitcherCore\Switcher\Core
     */
    function getCore($ip, $community, $login, $password) {
        $connector = (new CoreConnector(Helper::getBuildInConfig()))
            ->setLogger(new Logger("test"))
            ->setCache(new PhpCache());
        return $connector->init(Device::init($ip,
            $community,
            $login,
            $password
        )
            ->setPrivateCommunity($community)
            ->set('consoleConnectionType', conf('console.type'))
            ->set('consoleTimeout', conf('console.timeout'))
            ->set('consolePort', conf('console.port'))
            ->set('snmpRepeats', conf('snmp.repeats'))
            ->set('snmpTimeoutSec', conf('snmp.timeout'))
            ->set('mikrotikApiPort', conf('miktotik_api.port'))
        );
    }
}
