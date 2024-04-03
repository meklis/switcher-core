<?php

require __DIR__ . "/../vendor/autoload.php";

$displayModulesForTypes = [
    'OLT' => [
        'info' => [
            'system',
            'sys_resources',
            'card_status',
            'link_info',
            'fdb',
            'interface_descriptions',
            'interface_counters',
            'pon_onts_status',
            'pon_onts_mac_addr',
            'pon_onts_serial',
            'pon_onts_optical',
            'pon_ports_optical',
            'pon_onts_reasons',
            'pon_onts_vendor',
            'uni_interfaces_status',
            'gpon_profiles',
            'unregistered_onts',
            'pon_onts_down_history',
            'pon_onts_configuration',
        ],
        'management' => [
           'ctrl_ont_reboot',
           'ctrl_ont_disable',
           'ctrl_ont_descr',
           'ctrl_port_descr',
           'ctrl_ont_delete',
           'ctrl_ont_uni_admin_state',
           'save_config',
           'multi_console_command',
           'unregistered_onts',
        ],
    ],
    'SWITCH' => [
        'info' => [
            'system',
            'sys_resources',
            'vlans',
            'link_info',
            'interface_descriptions',
            'interface_counters',
            'errors',
            'pvid',
            'rmon',
            'fdb',
            'cable_diag',
        ],
        'management' => [
            'reboot',
            'save_config',
            'clear_counters',
            'ctrl_port_state',
            'ctrl_port_speed',
            'ctrl_port_descr',
            'multi_console_command',
        ],
    ],
    'ROUTER' => [
        'info' => [
            'system',
            'interface_info',
            'dhcp_server_info',
            'lease_info',
            'address_list_info',
            'bgp_sessions',
        ],
        'management' => [
        ],
    ],
    'SENSORS' => [
      'info' => [
            'system',
            'power_control_output_list',
            'digital_lines_list',
            'analog_lines_list',
            'power_sensor_state',
            'knock_sensor_state',
      ],
        'management' => [
            'ctrl_power_control_output',
            'ctrl_digital_line',
            'ctrl_analog_line',
        ]
    ],
];


$reader = new \SwitcherCore\Config\Reader(__DIR__ . "/../configs");
$devices = $reader->readModels();
$supportDevices = [];
foreach ($devices as $dev) {
    $supportDevices[$dev->getDeviceType()][$dev->getKey()]['name'] = $dev->getName();
    $supportDevices[$dev->getDeviceType()][$dev->getKey()]['key'] = $dev->getKey();
    $supportDevices[$dev->getDeviceType()][$dev->getKey()]['modules'] = $dev->getModulesList();
//    if ($dev->getRewrites() && isset($dev->getRewrites()['mapping'])) {
//        foreach ($dev->getRewrites()['mapping'] as $mapping) {
//            if (!isset($mapping['rewrite']['key'])) continue;
//            if (!isset($mapping['rewrite']['name'])) continue;
//            $supportDevices[$dev->getDeviceType()][$mapping['rewrite']['key']]['name'] = $mapping['rewrite']['name'];
//            $supportDevices[$dev->getDeviceType()][$mapping['rewrite']['key']]['key'] = $mapping['rewrite']['key'];
//            $supportDevices[$dev->getDeviceType()][$mapping['rewrite']['key']]['modules'] = $dev->getModulesList();
//        }
//    }
}
$modulesData = [];
foreach ($reader->readModulesConfig() as $module) {
    $modulesData[$module->getName()] = $module;
}


$buildTable = function ($modulesList, $devicesList) use ($modulesData)
{
    ksort($devicesList);
    $heads = '';
    foreach ($modulesList as $modName) {
        $module = $modulesData[$modName];
        $heads .= "<th class='module-name-header'>{$module->getDescr()}<br><span class='module-key-name'>{$module->getName()}</span></th>\n";
    }
    $hmodels = '';
    foreach ($devicesList as $device) {
        $hmodels .= "
            <tr><th class='device-name'>{$device['name']}</th>
        ";
        foreach ($modulesList as $moduleName ) {
            $exist = in_array($moduleName, $device['modules']) ? "+" : "-";
            $hmodels .= "<td class='".($exist === '+' ? 'flag-yes' : 'flag-not') ."'>{$exist}</td>";
        }
        $hmodels .= "</tr>";
    }
    $html = <<<HTML
<div class="container">
<table>
<thead>
<tr>
    <th class="headers">Device model</th>
    {$heads}
</tr>
<tbody>
{$hmodels}
</tbody>
</table>
</div>
HTML;
    return $html;
};


$html = "
<html>
 <head>
  <title>Wildcore support device information</title>
  <meta charset=\"utf-8\">
 </head>
<body>
<style>
    .container {
        max-width: 100%;
        margin: auto;
        margin-bottom: 2rem;
        overflow-x: auto;
    }
    tr>th:first-child,tr>td:first-child {
        position: sticky;
        left: 0;
    }
    table {
        border: 1px solid #D0D0D0;
        font-size: 95%;
        position: relative;
        table-layout: auto;
        border-collapse: collapse;
    }
    table thead tr th {
        border: 1px solid #D0D0D0;
        border-collapse: collapse;
        padding: 5px;
        min-width: 120px;
    }
    table tbody tr td {
        border: 1px solid #D0D0D0;
        border-collapse: collapse;
        padding: 5px;
    }
    th {
        background-color: #F0F0F0;
        border: 1px solid #D0D0D0;
    }
    .module-key-name {
        font-size: 90%;
        color: gray;
    }
    .device-name {
        border: 1px solid #D0D0D0;
        text-align: left; 
        width: 250px;
        min-width: 250px;
        padding: 5px;
    }
    .device-key {
        font-size: 90%;
        color: gray;
    }
    .flag-yes {
        background: #dcffd5;
    }
    .flag-not {
        background: #FAFAFA;
    }
</style>
    <h1>List of supported devices and available functionality</h1>
    <h2>OLTs</h2>
    <h3>Information</h3>
    ".$buildTable($displayModulesForTypes['OLT']['info'], $supportDevices['OLT'])."
    <h3>Management</h3>
    ".$buildTable($displayModulesForTypes['OLT']['management'], $supportDevices['OLT'])."
    
    <br>
    <h2>Switches</h2>
    <h3>Information</h3>
    ".$buildTable($displayModulesForTypes['SWITCH']['info'], $supportDevices['SWITCH'])."
    <h3>Management</h3>
    ".$buildTable($displayModulesForTypes['SWITCH']['management'], $supportDevices['SWITCH'])."
    <br>
    <h2>Routers</h2>
    <h3>Information</h3>
    ".$buildTable($displayModulesForTypes['ROUTER']['info'], $supportDevices['ROUTER'])."
   
    <br>
    <h2>Sensors</h2>
    <h3>Information</h3>
    ".$buildTable($displayModulesForTypes['SENSORS']['info'], $supportDevices['SENSORS'])."
    <h3>Management</h3>
    ".$buildTable($displayModulesForTypes['SENSORS']['management'], $supportDevices['SENSORS'])."
</body>
";

file_put_contents(__DIR__ . '/device_info.html', $html);