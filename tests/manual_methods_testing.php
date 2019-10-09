<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpSwitcher\Config\Reader;
use SnmpWrapper\WrapperWorker;
use SnmpWrapper\Walker;

$reader = new  Reader(__DIR__ . "/../configs");
$model = SnmpSwitcher\Config\ModelCollector::init($reader);
$oids = SnmpSwitcher\Config\OidCollector::init($reader);
$wrapper = new  WrapperWorker("http://37.57.212.3:8080");
$walker =  (new  Walker($wrapper))
    ->useCache(false);

$switcher = new \SnmpSwitcher\Switcher\SnmpSwitcher($walker,$model,$oids);

$handle = fopen ("php://stdin","r");

$switcher->connect($argv[1], $argv[2]);

echo "Start testing ...\n\n";

$evals = [
    'getSystemInfo()',
    'getFDB()',
    'getErrors()',
    'getRmon(3)',
    'getCounters(3)',
    'getPVID()',
    'getVlans()',
    'getVlansByPort()',
    'getCableDiag()',
    'getLinkInfo()',
];



foreach ($evals as $num=>$eval) {
    if(isset($argv[3])) {
        if($argv[3] > $num) continue;
    }
    echo "Step number {$num}, call method {$eval} \n";
    try {
        eval("print_r(json_encode(\$switcher->$eval, JSON_PRETTY_PRINT));");
    } catch (Exception $e) {
        echo "Method $eval has exception, fix it!\n";
        echo "Write num of step ($num) in arguments for running from this step.\n";
        throw new Exception($e);
    }
    echo "\nClick ENTER for continue...\n";
    fgets($handle);
}


echo "Diagnostic finished!\n";