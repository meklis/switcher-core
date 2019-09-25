<?php
require __DIR__ . "/../vendor/autoload.php";

$reader = new \Switcher\Config\Reader(__DIR__ . "/../configs");



print_r($reader->readGlobalOids());