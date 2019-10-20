<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;

$reader = new  Reader(__DIR__ . "/../configs");

$wrapper = new  WrapperWorker("http://37.57.212.3:8080");
$walker =  (new  Walker($wrapper))
    ->useCache(false);

$switcher = new \SwitcherCore\Switcher\Switcher($walker,$reader);
$switcher->connect($argv[1], $argv[2]);

$snmp_switcher = new \SwitcherCore\Switcher\SnmpSwitcher($switcher);

$handle = fopen ("php://stdin","r");

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
    'clearCounters()',
    'rebootDevice()',
];



foreach ($evals as $num=>$eval) {
    if(isset($argv[3])) {
        if($argv[3] > $num) continue;
    }
    echo "Step number {$num}, call method {$eval} \n";
    try {
        eval("print_r(json_encode(\$snmp_switcher->$eval, JSON_PRETTY_PRINT));");
    } catch (Exception $e) {
        echo "Method $eval has exception, fix it!\n";
        echo "Write num of step ($num) in arguments for running from this step.\n";
        throw new Exception($e);
    }
    echo "\nClick ENTER for continue...\n";
    fgets($handle);
}


echo "Diagnostic finished!\n";