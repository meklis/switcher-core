<?php
require __DIR__ . "/../vendor/autoload.php";

use \SwitcherCore\Modules\Helper;
use \SwitcherCore\Switcher\CoreConnector;

//10.15.1.2 billing test test
$C = [
    'ip' => '10.15.1.2',
    'community' => 'billing',
    'login' => 'test',
    'password' => 'test',
    'module' => 'pon_uni_info',
];

$ARGUMENTS = [];

$connector = (new CoreConnector(Helper::getBuildInConfig(), __DIR__ . '/../configs/proxies.yml'))
    ->setCache(new \SwitcherCore\Switcher\PhpCache());

$core = $connector->init(\SwitcherCore\Switcher\Device::init($C['ip'], $C['community'], $C['login'], $C['password'])
    ->set('telnetTimeout', 10)
    ->set('telnetPort', 23)
    ->set('snmpRepeats', 3)
    ->set('snmpTimeoutSec', 2)
    ->set('mikrotikApiPort', 2)
);

//Prepare modules list
$modules = $core->getModulesData();
usort($modules, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

$module = null;
foreach ($modules as $m) {
    if($m['name'] === $C['module']) {
        $module = $m;
        break;
    }
}


try {
    echo "==========================RESPONSE===============================\n";
    echo "\$parameters=json_decode('" . json_encode($ARGUMENTS) . "', true);\n";
    echo "\$core->action('{$module['name']}', \$parameters);\n\n";
    echo json_encode($core->action($module['name'], $ARGUMENTS), JSON_PRETTY_PRINT);
    echo "\n==============================================================\n\n\n";
    echo "Diagnostic finished!\n";
} catch (\Exception $e) {
    echo "\n==================DUMP OF TELNET CONNECTION===============\n";
    throw new Exception($e);
}
