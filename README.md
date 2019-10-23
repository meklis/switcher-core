# Switcher-Core
### Module for help-provider services

## Overview
It's library allow work with devices using a single interface - Core.
Library automaticaly detect model, load device configuration and modules for work with it.   
    
Switcher-core using modules for work with devices and allow extend the kernel with your own modules.    
Now library has two inputs - snmp and telnet, in feature inputs will be extend.

### Requirements   
* Telnet-Proxy - https://github.com/meklis/telnet-proxy    
* SnmpWalk-Proxy - https://github.com/meklis/http-snmpwalk-proxy    

*Use https://github.com/meklis/switcher instructions and docker-compose files for easy start proxies* 

Work only with PHP >=7.2

### Install
```
composer install meklis/switcher-core
```


### Customization
You can add own devices models and create own modules using core.    

For using customization you can copy configuration directory to you directory
``` 
project_dir$ cp -R vendor/meklis/switcher-core/configs ./switcher-config
```
In configuration file you may add models.   

### Build-in modules
[Modules response example](docs/MODULES.md)
