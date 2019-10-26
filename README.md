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

### Requirements   
* Telnet-Proxy - https://github.com/meklis/telnet-proxy    
* SnmpWalk-Proxy - https://github.com/meklis/http-snmpwalk-proxy    

*Use https://github.com/meklis/switcher instructions and docker-compose files for easy start proxies* 

It works only with PHP >=7.2

### Install
```
composer install meklis/switcher-core
```


### Customization
You can add own devices models and create own modules using core.    

For using the customization you must copy this configuration directory to you own dir. Than you can use it for your customization.
``` 
project_dir$ cp -R vendor/meklis/switcher-core/configs ./switcher-config
```
In configuration files you may add models.      
You can see in commit [7e368f7f](https://github.com/meklis/switcher-core/commit/7e368f7f4970a66b3c1a91ac174ce72e12e42725) how to add new module, for example.
    
### Build-in modules
[Modules response example](docs/MODULES.md)
