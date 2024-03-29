<?php
require __DIR__ . "/../vendor/autoload.php";

use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\PhpCache;


$handle = fopen("php://stdin", "r");

$connector = (new CoreConnector(Helper::getBuildInConfig()))
    ->setCache(new PhpCache());

$core = $connector->init(Device::init($argv[1], $argv[2], $argv[3], $argv[4])
    ->set('telnetTimeout', 10)
    ->set('telnetPort', 23)
    ->set('snmpRepeats', 3)
    ->set('snmpTimeoutSec', 2)
    ->set('mikrotikApiPort', 55055)
);

//Prepare modules list
$modules = $core->getModulesData();
usort($modules, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});


STEP_CHOOSING:
echo "Choose module:\n";
foreach ($modules as $num => $module) {
    echo "{$num})\t{$module['name']}\n";
}
echo "\n";
echo "Module num: ";
$step_num = trim(fgets($handle));
if (!isset($modules[$step_num])) {
    echo "\nIncorrect step number\n\n\n";
    goto STEP_CHOOSING;
}
$module = $modules[$step_num];
echo "Start testing module {$module['name']} ({$module['class']})\n";

$params = [];
if ($module['arguments']) {
    echo "Module has params, setup please\n";
    echo "Params: \n";
    foreach ($module['arguments'] as $argument) {
        ARGUMENT_INPUT:
        $star = $argument['required'] ? "*" : "";
        echo "   {$star}{$argument['name']}: ";
        $param = trim(fgets($handle));
        if (!$param && !$argument['required']) {
            continue;
        }
        if (!preg_match("/{$argument['pattern']}/", $param)) {
            echo "Incorrect param. Param has pattern: {$argument['pattern']}\n";
            goto ARGUMENT_INPUT;
        } else {
            $params[$argument['name']] = $param;
        }
    }
}
    echo "==========================RESPONSE===============================\n";
    echo "\$parameters=json_decode('" . json_encode($params) . "', true);\n";
    echo "\$core->action('{$module['name']}', \$parameters);\n\n";
    $start = microtime(true);
    try {
        $response = $core->action($module['name'], $params);
    } catch (\Throwable $e) {
        /**
         * @var \SwitcherCore\Switcher\Console\TelnetLazyConnect $telnet
         */
        $telnet = $core->getContainer()->get(\SwitcherCore\Switcher\Console\TelnetLazyConnect::class);
        echo "Global telnet buffer: \n";
        echo $telnet->getGlobalBuffer();
        echo "==================================\n";

        throw $e;
    }
    file_put_contents(__DIR__ . '/../docs/modules_example_output/' . $module['name'] . '.json', json_encode(['data' => $response, 'arguments' => $params], JSON_PRETTY_PRINT));
    echo json_encode($response, JSON_PRETTY_PRINT);
    $time = round(microtime(true) - $start, 3);
    echo "\n==============================================================\n\n\n";
    echo "Diagnostic finished with time $time sec!\n";


goto STEP_CHOOSING;