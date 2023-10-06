<?php

namespace SwitcherCore\Dev;

use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\PhpCache;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CallOneModuleWithModule extends AbstractCommand
{

    function configure()
    {
        $this->setName('call')
            ->addArgument('module', InputArgument::REQUIRED, "Call some module")
            ->addArgument('ip', InputArgument::REQUIRED, "Device IP")
            ->addOption("model", "m", InputOption::VALUE_OPTIONAL, "Preseted model key", null)
            ->addOption("no-check-alive", "no-alive", InputOption::VALUE_NONE, "No check over snmp")
            ->addOption("community", "c", InputOption::VALUE_OPTIONAL, "Community", "public" )
            ->addOption("username", "u", InputOption::VALUE_OPTIONAL, "Console username", "admin" )
            ->addOption("password", "p", InputOption::VALUE_OPTIONAL, "Console password", "" )
            ->addOption("arguments", "a",InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Arguments")
            ->addOption("telnet", "t",InputOption::VALUE_NONE, "Print telnet output")
            ->setDescription("Call module with arguments");
    }

    function exec(InputInterface $input, OutputInterface $output)
    {
        $core = $this->getCore();
        $output->writeln("<comment>Calling module with name={$input->getArgument('module')} for device with name={$core->getDeviceMetaData()['name']}, key={$core->getDeviceMetaData()['key']}</comment>");
        $arguments = [];
        foreach ($input->getOption('arguments') as $argkv) {
            list($key, $value) = @explode("=", $argkv);
            $arguments[$key] = $value;
        }
        $data = $core->action($input->getArgument('module'), $arguments);
        $output->writeln(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        if($input->getOption('telnet')) {
            $output->writeln("<comment>=================================== Console output ==================================</comment>");
            echo $core->getContainer()->get(ConsoleInterface::class)->getGlobalBuffer();
            $output->writeln("\n<comment>====================================================================================</comment>");
        }
        return 0;
    }

    function getCore() {
        $connector = (new CoreConnector(Helper::getBuildInConfig()))
            ->setCache(new PhpCache());
        return $connector->init(Device::init(
            $this->input->getArgument('ip'),
            $this->input->getOption('community'),
            $this->input->getOption('username'),
            $this->input->getOption('password'),
        )   ->setPrivateCommunity($this->input->getOption('community'))
            ->setCheckAlive(!$this->input->getOption('no-check-alive'))
            ->setModelKey($this->input->getOption('model'))
            ->set('consoleConnectionType', conf('console.type'))
            ->set('consoleTimeout', conf('console.timeout'))
            ->set('consolePort', conf('console.port'))
            ->set('snmpRepeats', conf('snmp.repeats'))
            ->set('snmpTimeoutSec', conf('snmp.timeout'))
            ->set('mikrotikApiPort', conf('miktotik_api.port'))
        );
    }
}
