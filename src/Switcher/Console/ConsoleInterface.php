<?php

namespace SwitcherCore\Switcher\Console;

use Meklis\Network\Console\Helpers\HelperInterface;

interface ConsoleInterface
{
    function getGlobalBuffer();
    function exec($command, $add_newline = true, $prompt = null);
    function write($buffer, $add_newline = true);
    function waitPrompt($prompt = null);
    function getBuffer();
    function connect($host, $port, HelperInterface $helper);
    function disconnect();
    function setAccess($username, $password);
    function setHost($host,$port = 23);
    function setDeviceHelper(HelperInterface $helper);

    function connectAndLogin();
    function setTimeout($timeout);
    function setStreamTimeout($timeout);
    function getStream();

    /**
     * @return \Meklis\Network\Console\Helpers\DefaultHelper|HelperInterface
     */
    function getDeviceHelper();
}