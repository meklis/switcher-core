<?php

require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");

$modules = $reader->readModulesConfig();
$moduleData = [];
foreach ($modules as $module) {
    $moduleData[$module->getName()] = [
        'descr' => $module->getDescr(), 
        'arguments' => $module->getArguments(),
    ];
}


ksort($moduleData);
$MARKDOWN = "### Список поддерживаемых модулей";
foreach ($moduleData as $name=>$data) {
    $arguments = "";
    if($data['arguments']) {
        $arguments .= "    \n";
        $arguments .= "**Аргументы:**    \n";
        foreach ($data['arguments'] as $argument) {
            if ($argument['required']) {
                $req = ", обязательный";
            } else {
                $req = "";
            }
            $arguments .= "- **{$argument['name']}**, проверка выражением: *{$argument['pattern']}*$req    \n";
        }
    }

    $MARKDOWN .= <<<MARKDOWN
    
    
### [{$name}](#{$name}) - {$data['descr']} 
$arguments 
MARKDOWN;
}
file_put_contents(__DIR__ . '/MODULES.md', $MARKDOWN);