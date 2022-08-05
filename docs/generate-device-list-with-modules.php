<?php

require  __DIR__ . "/../vendor/autoload.php";

$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");


$devices = $reader->readModels();
$supportDevices = [];
foreach ($devices as $dev) {
    $supportDevices[$dev->getKey()]['name'] = $dev->getName();
    $supportDevices[$dev->getKey()]['key'] = $dev->getKey();
    $supportDevices[$dev->getKey()]['modules'] = $dev->getModulesList();
    if($dev->getRewrites() && isset($dev->getRewrites()['mapping'])) {
        foreach ($dev->getRewrites()['mapping'] as $mapping) {
            if(!isset($mapping['key'])) continue;
            if(!isset($mapping['name'])) continue;
            $supportDevices[$mapping['key']]['name'] = $mapping['name'];
            $supportDevices[$mapping['key']]['key'] = $mapping['key'];
            $supportDevices[$mapping['key']]['modules'] = $mapping['modules'];
        }
    }
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