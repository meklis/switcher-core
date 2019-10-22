<?php
require __DIR__ . "/../vendor/autoload.php";

use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;

$handle = fopen ("php://stdin","r");


//Switcher core initialization
$walker =  (new  Walker(
        new  WrapperWorker("http://37.57.212.3:8080"))
    )->useCache(false)
    ->setIp($argv[1])
    ->setCommunity($argv[2]);
$telnet = (new \SwitcherCore\Switcher\Objects\TelnetLazyConnect($argv[1], 23))
    ->connectOverProxy("tcp://37.57.212.3:3333")
    ->login($argv[3], $argv[4]);
$core = (new \SwitcherCore\Switcher\Core(
    new  Reader(__DIR__ . "/../configs")
))->setTelnet($telnet)->setWalker($walker)->detectModel();



//Prepare modules list
$modules = $core->getModulesData();
$evals = [];
STEP_CHOOSING:
echo "Supported modules:\n";
foreach ($modules as $num=>$module) {
    $evals[] = ['name'=> $module['name'], 'argv' => '[]', 'module' => $module['class']];
    echo "{$num})     {$module['name']}\n";
}
echo "Write num of step or click enter for all modules testing\n";
echo "Step: ";
$step_num = trim(fgets($handle));
if($step_num) {
    echo "Choosed step number {$step_num}, call method {$evals[$step_num]['name']}({$evals[$step_num]['argv']}) - {$evals[$step_num]['module']} \n";
    print_r(json_encode($core->action($evals[$step_num]['name']), JSON_PRETTY_PRINT));
    echo "Diag finished!\n";
    goto STEP_CHOOSING;
}

echo "Start testing...\n";
sleep(1);
foreach ($evals as $num=>$eval) {
    if(isset($argv[5])) {
        if($argv[5] > $num) continue;
    }
    echo "Step number {$num}, call method {$eval['name']}({$eval['argv']}) - {$eval['module']} \n";
    try {
        echo "### Module {$eval['module']}    \n";
        echo "```
        json_encode(\$core->action('{$eval['name']}', ['port'=>3]), JSON_PRETTY_PRINT);    \n";
        echo json_encode($core->action($eval['name'], ['port'=>27]), JSON_PRETTY_PRINT);
        echo "
```\n";
        //eval("print_r(json_encode(\$core->action('{$eval['name']}', {$eval['argv']}), JSON_PRETTY_PRINT));");
    } catch (Exception $e) {
        echo "Method {$eval['name']} has exception, fix it!\n";
        echo "Write num of step ($num) in arguments for running from this step.\n";
        throw new Exception($e);
    }
    echo "\nClick ENTER for continue...\n";
    fgets($handle);
}


echo "Diagnostic finished!\n";