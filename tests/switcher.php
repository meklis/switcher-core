<?php
require __DIR__ . "/../vendor/autoload.php";

$reader = new  Reader(__DIR__ . "/../configs");
$model = Switcher\Config\ModelCollector::init($reader);
$oids = Switcher\Config\OidCollector::init($reader);
$wrapper = new  WrapperWorker("http://127.0.0.1:8080");
$walker =  (new  Walker($wrapper))
    ->useCache(false);

$switcher = new Switcher($walker,$model,$oids);

$switcher->connect('10.50.124.132', 'kievsnmprw');

print_r($switcher->getSystemInfo());