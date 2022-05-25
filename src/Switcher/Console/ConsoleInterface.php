<?php

namespace SwitcherCore\Switcher\Console;

use Meklis\Network\Console\Helpers\HelperInterface;

interface ConsoleInterface
{
    function getGlobalBuffer();
    function exec($command, $add_newline = true, $prompt = null);
    function connect($host, $port, HelperInterface $helper);
    function disconnect();
    function setAccess($username, $password);
    function setHost($host,$port = 23);
    function setDeviceHelper(HelperInterface $helper);
}