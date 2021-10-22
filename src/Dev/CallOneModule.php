<?php

namespace SwitcherCore\Dev;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CallOneModule extends AbstractCommand
{

    function configure()
    {
        $this->setName('sc:action')
            ->addArgument('module', InputArgument::REQUIRED, "Call some module")
            ->addDefaultArguments()
            ->addArgument("arguments", InputArgument::OPTIONAL, "Arguments")
            ->setDescription("Return list of modules by device");
    }

    function exec(InputInterface $input, OutputInterface $output)
    {
        $core = $this->getCore();
        $output->writeln("<comment>Calling module with name={$input->getArgument('module')} for device with name={$core->getDeviceMetaData()['name']}, key={$core->getDeviceMetaData()['key']}</comment>");
        $arguments = [];
        foreach ($input->getArgument('arguments') as $argkv) {
            list($key, $value) = @explode("=", $argkv);
            $arguments[$key] = $value;
        }
        $data = $core->action($input->getArgument('module'), $arguments);
        $output->writeln(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

}
