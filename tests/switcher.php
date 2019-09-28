<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpSwitcher\Config\Reader;
use SnmpSwitcher\Switcher\Switcher;
use SnmpWrapper\WrapperWorker;
use SnmpWrapper\Walker;

$reader = new  Reader(__DIR__ . "/../configs");
$model = SnmpSwitcher\Config\ModelCollector::init($reader);
$oids = SnmpSwitcher\Config\OidCollector::init($reader);
$wrapper = new  WrapperWorker("http://127.0.0.1:8080");
$walker =  (new  Walker($wrapper))
    ->useCache(false);

$switcher = new Switcher($walker,$model,$oids);

$switcher->connect('10.50.124.132', 'kievsnmprw');

 $switcher->getLinkInfo() ;