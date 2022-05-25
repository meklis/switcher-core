<?php

namespace SwitcherCore\Dev;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListOfSupportedModules extends AbstractCommand
{

    function configure()
    {
        $this->setName('sc:modules')
            ->addDefaultArguments()
            ->setDescription("Return list of modules by device");
    }

    function exec(InputInterface $input, OutputInterface $output)
    {
        $core = $this->getCore();
        $modules = $core->getModulesData();
        $output->writeln("<comment>Supported modules list for device with name={$core->getDeviceMetaData()['name']}, key={$core->getDeviceMetaData()['key']}</comment>");
        $table = new Table($output);
        $table->setHeaders([
            'Module name',
            'Called class',
            'Arguments',

        ]);
        foreach ($modules as $module) {
            $arguments = '';
            foreach ($module['arguments'] as $argData) {
                $req = $argData['required'] ? "*" : "";
                $arguments .= "{$argData['name']}$req, pattern: /{$argData['pattern']}/\n";
            }
            $table->addRow([
                $module['name'],
                $module['class'],
                trim($arguments),
            ]);
        }
        $table->render();
        return 0;
    }

}
