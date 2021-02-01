<?php
require __DIR__ . "/../vendor/autoload.php";

use \SwitcherCore\Modules\Helper;
use \SwitcherCore\Switcher\CoreConnector;


$handle = fopen ("php://stdin","r");

$connector = (new CoreConnector(Helper::getBuildInConfig(), __DIR__ . '/../configs/proxies.yml'))
->setTelnetPort(23)->setMikrotikApiPort(55055);

$core = $connector->init($argv[1], $argv[2], $argv[3], $argv[4]);

//Prepare modules list
$modules = $core->getModulesData();
usort($modules, function($a, $b) {return strcmp($a['name'], $b['name']);});


STEP_CHOOSING:
echo "Choose module:\n";
foreach ($modules as $num=>$module) {
    echo "{$num})\t{$module['name']}\n";
}
echo "\n";
echo "Module num: ";
$step_num = trim(fgets($handle));
if(!isset($modules[$step_num])) {
    echo "\nIncorrect step number\n\n\n";
    goto STEP_CHOOSING;
}
$module = $modules[$step_num];
echo "Start testing module {$module['name']} ({$module['class']})\n";

$params = [];
if($module['arguments']) {
    echo "Module has params, setup please\n";
    echo "Params: \n";
    foreach ($module['arguments'] as $argument) {
        ARGUMENT_INPUT:
        $star = $argument['required'] ? "*" : "";
        echo "   {$star}{$argument['name']}: ";
        $param = trim(fgets($handle));
        if(!$param && !$argument['required']) {
            continue;
        }
        if(!preg_match("/{$argument['pattern']}/", $param)) {
            echo "Incorrect param. Param has pattern: {$argument['pattern']}\n";
            goto ARGUMENT_INPUT;
        }  else {
            $params[$argument['name']] = $param;
        }
    }
}
try {
    echo "==========================RESPONSE===============================\n";
    echo "\$parameters=json_decode('" . json_encode($params) . "', true);\n";
    echo "\$core->action('{$module['name']}', \$parameters);\n\n";
    echo json_encode($core->action($module['name'], $params), JSON_PRETTY_PRINT);
    echo "\n==============================================================\n\n\n";
    echo "Diagnostic finished!\n";
} catch (\Exception $e) {
    echo "\n==================DUMP OF TELNET CONNECTION===============\n";
    echo $core->getInternalObjects()->telnet->getGlobalBuffer();
    throw new Exception($e);
}

goto STEP_CHOOSING;