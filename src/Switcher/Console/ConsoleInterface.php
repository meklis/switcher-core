<?php

namespace SwitcherCore\Switcher\Console;

interface ConsoleInterface
{
    function setHostType($hostType);
    function login($username, $password, $host_type);
    function addCommandAfterLogin($command);
    function exec($command, $add_newline = true, $prompt = null);
    function connect();
    function disconnect();

}