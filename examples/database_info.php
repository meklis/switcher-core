<?php
require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");

$modules = $reader->readModulesConfig();
$moduleData = [];
foreach ($modules as $module) {
    $moduleData[$module->getName()] = [
        'descr' => $module->getDescr(),
        'depends' => $module->getDependencyModules(),
        'arguments' => $module->getArguments(),
    ];
}

$devices = $reader->readModels();
$supportDevices = [];
foreach ($devices as $dev) {
    foreach ($dev->getModulesListAssoc() as $moduleName=>$moduleClassName) {
        $supportDevices[$moduleName][$dev->getName()] = $moduleClassName;
    }
}


ksort($moduleData);
foreach ($moduleData as $name=>$data) {
    $arguments = "";
    if($data['arguments']) {
        $arguments .= "    \n";
        $arguments .= "**Arguments:**    \n";
        foreach ($data['arguments'] as $argument) {
            if ($argument['required']) {
                $req = ", required";
            } else {
                $req = "";
            }
            $arguments .= "- **{$argument['name']}**, pattern: *{$argument['pattern']}*$req    \n";
        }
    }
    $devices = "";
    if(isset($supportDevices[$name])) {
        $devices .= "**Supported devices:**    \n";
        foreach ($supportDevices[$name] as $dev=>$className) {
            $devices .= "- {$dev}  *($className)*   \n";
        }
    }
    echo <<<MARKDOWN
    
    
### {$name} - {$data['descr']}
Name: **{$name}**   
$arguments
$devices
MARKDOWN;
}