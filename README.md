# Switcher-Core
### Module for help-provider services

## Overview
This library allow works with devices using a single interface - Core.
Library automaticaly detect model, load device configuration and modules for work with it.   
    
Switcher-core using modules for work with devices and allow extend the kernel with your own modules.    
Now library has three inputs, in feature inputs will be extend.    

### Supported inputs
* Telnet
* SNMP
* RouterOS API

### Supported vendors
- [x] D-link (snmp/telnet)
- [x] Mikrotik, RouterOS (snmp/RouterOS api)
- [ ] EdgeCore
- [ ] BDcom
- [ ] Xtreme
- [ ] ZTE
- [ ] Huawei

### Requirements   
* Telnet-Proxy - https://github.com/meklis/telnet-proxy    
* SnmpWalk-Proxy - https://github.com/meklis/http-snmpwalk-proxy    

*Use https://github.com/meklis/switcher instructions and docker-compose files for easy start proxies* 

It works only with PHP >=7.2

### Install
```
composer install meklis/switcher-core
```

### How to use
``` 
require __DIR__ . "/../vendor/autoload.php";

use SnmpWrapper\Walker;
use SnmpWrapper\WrapperWorker;
use SwitcherCore\Config\Reader;
use SwitcherCore\Config\ProxyConfiguration;

$ip = '10.90.90.90';
$community = 'public';
$snmp_proxy_addr = 'http://127.0.0.1:3332';

//Switcher core initialization
$walker =  (new  Walker(
        new  WrapperWorker($snmp_proxy_addr)
    )->useCache(false)
    ->setIp($ip)
    ->setCommunity($community);

$core = (new \SwitcherCore\Switcher\Core(
    new  Reader( __DIR__ . "/vendor/meklis/switcher-core/configs")
))->setWalker($walker)->init();

//Get system info
echo json_encode($core->action('system'), JSON_PRETTY_PRINT);

/**
{
    "descr": "RouterOS RB952Ui-5ac2nD",
    "uptime": "8d 9h 55min 32sec",
    "contact": "",
    "name": "G_OfficeMik",
    "location": "",
    "meta": {
        "name": "Mikrotik RB952Ui-5ac2nD",
        "detect": {
            "description": "^RouterOS RB952Ui-5ac2nD$",
            "objid": "^.1.3.6.1.4.1.14988.1$"
        },
        "ports": 0,
        "extra": [],
        "modules": [
            "system",
            "arp_info",
            "arp_ping",
            "interface_vlan_info",
            "dhcp_server_info",
            "lease_info",
            "ctrl_static_arp",
            "ctrl_static_lease"
        ]
    }
}
*/

```

    
### Build-in modules
[Modules response example](docs/MODULES.md)

### Customization
You can add own devices models and create own modules using core.    

For using the customization you must copy this configuration directory to you own dir. Than you can use it for your customization.
``` 
project_dir$ cp -R vendor/meklis/switcher-core/configs ./switcher-config
```
In configuration files you may add models.      
You can see in commit [7e368f7f](https://github.com/meklis/switcher-core/commit/7e368f7f4970a66b3c1a91ac174ce72e12e42725) how to add new module, for example.

