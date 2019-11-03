<?php

require __DIR__ . "/../vendor/autoload.php";

//Switcher core initialization
use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;
use SwitcherCore\Switcher\Core;

$device_ip = '10.0.0.1';
$community = 'public';
$telnet_username = 'user';
$telnet_password = 'pass';

$snmp_walker_proxy_address = "http://127.0.0.1:8080";
$telnet_proxy_addr = "tcp://127.0.0.1:3333";

$configuration_path = __DIR__ . "/../configs";

//Initialize snmp walker
$snmpProxy =  new  WrapperWorker($snmp_walker_proxy_address);
$walker =  (new  Walker($snmpProxy))->useCache(false)
    ->setIp($device_ip)
    ->setCommunity($community);

//Initialize telnet client
//In example using build-in TelnetLazyConnect
$telnet = (new TelnetLazyConnect($argv[1], 23))
    ->connectOverProxy()
    ->login($argv[3], $argv[4]);


$core = new Core(new  Reader($configuration_path));

//Include inputs
$core->setTelnet($telnet)->setWalker($walker);

//Detect device
//Retured Exception if not found(or another error) $this
$core->detectModel();


//Get some data
var_dump($core->action('system'));

