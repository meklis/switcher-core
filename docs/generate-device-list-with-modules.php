<?php

require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");


$devices = $reader->readModels();
$supportDevices = [];
foreach ($devices as $dev) {
    $supportDevices[$dev->getName()]['name'] = $dev->getName();
    $supportDevices[$dev->getName()]['key'] = $dev->getKey();
    $supportDevices[$dev->getName()]['modules'] = $dev->getModulesList();
}


ksort($supportDevices);
$MARKDONW = "# Список поддерживаемого оборудования и модулей  \n";
foreach ($supportDevices as $data) {
    $modules = '';
    foreach ($data['modules'] as $module) {
        $modules .= "[{$module}](MODULES.md#{$module}), ";
    }
    $modules = trim($modules, ', ');
    $MARKDONW .= "## {$data['name']} ({$data['key']})    \n";
    $MARKDONW .= trim($modules, ", ") . "           \n";
}
$MARKDONW .= "\n\n\n";

file_put_contents(__DIR__ . '/DEVICES.md', $MARKDONW);