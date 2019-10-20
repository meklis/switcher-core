<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;

//Switcher core initialization
$reader = new  Reader(__DIR__ . "/../configs");
$walker =  (new  Walker(
    new  WrapperWorker("http://37.57.212.3:8080")
))->useCache(false)->setIp($argv[1]);

$telnet = (new \Meklis\TelnetOverProxy\Telnet($argv[1], 23))
    ->setLazyConnect(true)
    ->connectOverProxy("tcp://127.0.0.1:3333");

$core = new \SwitcherCore\Switcher\Core($reader);


$core->setWalker($walker, $argv[2])->detectModel()->setTelnet($telnet, $argv[3], $argv[4]);



$handle = fopen ("php://stdin","r");

echo "Start testing ...\n\n";

$evals = [
    ['name'=>'system', 'argv' => '[]'],
];



foreach ($evals as $num=>$eval) {
    if(isset($argv[5])) {
        if($argv[5] > $num) continue;
    }
    echo "Step number {$num}, call method {$eval['name']}({$eval['argv']}) \n";
    try {
        eval("print_r(json_encode(\$core->action('{$eval['name']}', {$eval['argv']}), JSON_PRETTY_PRINT));");
    } catch (Exception $e) {
        echo "Method {$eval['name']} has exception, fix it!\n";
        echo "Write num of step ($num) in arguments for running from this step.\n";
        throw new Exception($e);
    }
    echo "\nClick ENTER for continue...\n";
    fgets($handle);
}


echo "Diagnostic finished!\n";