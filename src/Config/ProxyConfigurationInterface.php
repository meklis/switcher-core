<?php


namespace SwitcherCore\Config;


interface ProxyConfigurationInterface
{
    function getSnmpConfiguration();
    function getTelnetConfiguration();
    function setSearchedIp($ip_addr);

}