<?php

require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");


$devices = $reader->readModels();
$supportDevices = [];
foreach ($devices as $dev) {
    $supportDevices[$dev->getName()]['name'] = $dev->getName();
    $supportDevices[$dev->getName()]['modules'] = $dev->getModulesList();
}


ksort($supportDevices);
$MARKDONW = "###Список поддерживаемого оборудования со списком модулей    \n";
foreach ($supportDevices as $data) {
    $modules = '';
    foreach ($data['modules'] as $module) {
        $modules .= "[{$module}](docs/MODULES.md#{$module}), ";
    }
    $modules = trim($modules, ', ');
    $MARKDONW .= "* **{$data['name']}** - {$modules}           \n";
}
$MARKDONW .= "\n\n\n";

file_put_contents(__DIR__ . '/DEVICES.md', $MARKDONW);