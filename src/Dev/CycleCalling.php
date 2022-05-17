<?php

namespace SwitcherCore\Dev;

use SwitcherCore\Switcher\Core;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CycleCalling extends AbstractCommand
{

    function configure()
    {
        $this->setName('sc:call')
            ->addDefaultArguments()
            ->setDescription("Cycle calling");
    }

    function exec(InputInterface $input, OutputInterface $output)
    {
        $core = $this->getCore();
        $output->writeln("<comment>Choosed device with name={$core->getDeviceMetaData()['name']}, key={$core->getDeviceMetaData()['key']}</comment>");
        if($output->isVerbose()) {
            $this->renderModules($core, $output);
        }
        $modules = $this->getModulesList($core);
        $callback = function (string $userInput) use ($modules) {
            return array_filter($modules, function ($e) use ($userInput) {
                return preg_match("#^" . addslashes($userInput) . "#", $e);
            });
        };

        CHOOSE_MODULE:
        $question = new Question('Please provide the module name: ');
        $question->setAutocompleterCallback($callback);
        $moduleName = $this->getHelper('question')->ask($input, $output, $question);
        if (!$moduleName || !in_array($moduleName, $modules)) {
            $output->writeln("<error>Module with name '$moduleName' doesnt exist!</error>");
            goto CHOOSE_MODULE;
        }
        $output->writeln("<comment>Choosed module - {$moduleName}</comment>");
        $module = array_values(array_filter($core->getModulesData(), function ($e) use ($moduleName) {
            return $e['name'] === $moduleName;
        }))[0];
        $arguments = [];
        if ($module['arguments']) {
            $output->writeln("<comment>Module has arguments. Please, provide them </comment>");
            foreach ($module['arguments'] as $argument) {
                $helper = $this->getHelper('question');
                $question = new Question("{$argument['name']}: ", false);
                while (true) {
                    $response = $helper->ask($input, $output, $question);
                    if(!$argument['required'] && !$response) {
                        break;
                    }

                    if($argument['required'] && !$response) {
                        $output->writeln("<error>Argument {$argument['name']} is required!</error>");
                        continue;
                    }
                    if(!preg_match("#{$argument['pattern']}#", $response)) {
                        $output->writeln("<error>Argument {$argument['name']} has isn't correct value! Pattern: /{$argument['pattern']}/</error>");
                        continue;
                    }
                    break;
                }
                if($response) {
                    $arguments[$argument['name']] = $response;
                }
            }
        }
        $output->writeln("<comment>All arguments received, start calling...</comment>");
        $start = microtime(true);
        try {
            $response = $core->action($module['name'], $arguments);
        } catch (\Throwable $e) {
            $output->writeln("<error>

   Error call module with message: {$e->getMessage()}
</error>
");
            $output->writeln("In: {$e->getFile()}:{$e->getLine()}");
            $output->writeln("Exception trace:");
            foreach ($e->getTrace() as $tr) {
                if (isset($tr['class']) && isset($tr['function'])) {
                    $output->write("\t {$tr['class']}{$tr['type']}{$tr['function']} at ");
                }
                if (isset($tr['file']) && isset($tr['line'])) {
                    $output->writeln("<info>{$tr['file']}:{$tr['line']}</info>");
                }
            }
        }
        $time = microtime(true) - $start;
        $output->writeln(json_encode($response, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE));
        if ($output->isVerbose()) {
            $output->writeln("<info>=================================================================================</info>");
            $output->writeln("<info>\tSpent time: " . round($time, 4) . " sec</info>");
            $output->writeln("<info>=================================================================================</info>");
        }
        goto CHOOSE_MODULE;
    }

    function getModulesList(Core $core)
    {
        return array_map(function ($e) {
            return $e['name'];
        }, $core->getModulesData());
    }

    function renderModules(Core $core, OutputInterface $output)
    {
        $modules = $core->getModulesData();
        $output->writeln("<comment>Supported modules list</comment>");
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
    }
}
