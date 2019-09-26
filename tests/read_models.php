<?php
require __DIR__ . "/../vendor/autoload.php";

$reader = new \Switcher\Config\Reader(__DIR__ . "/../configs");
$model = Switcher\Config\ModelCollector::init($reader)->getModelByDetect('D-Link DES-3028');
$oids = Switcher\Config\OidCollector::init($reader)->readEnterpriceOids($model);
