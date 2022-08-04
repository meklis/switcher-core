# Switcher-Core
### Библиотека PHP для работы с сетевым оборудованием 

## Обзор
Библиотека позволяет работать с устройствами через единый интерфейс - нет необходимости писать интерфейсы и врапперы под каждого вендора, используйте модули.
Набор модулей и возвращаемый результат может отличаться в зависимости от типа оборудования, но это необходимая жертва. 

Так же есть возможность доработки своих модулей, на случай, если вашего оборудования не будет в списке - просто сделайте fork). 

### Поддерживаемые интерфейсы 
* Telnet
* SSH 
* SNMP
* RouterOS API

### Поддерживаемые вендоры
- [x] D-link switches 
- [x] Huawei switches  
- [x] EdgeCore switches
- [ ] Xtreme routers
- [x] Huawei OLTs
- [x] BDcom OLTs
- [x] ZTE OLTs
- [x] C-Data OLTs
- [x] V-Solution OLTs
- [x] Mikrotik routers

### [Полный список поддерживаемого оборудования и их модулей](docs/DEVICES.md)     
### [Список модулей](docs/MODULES.md)    

### Необходимо для начала работы   
PHP >= 7.2    
Модули PHP: yaml, zip, curl, json, mbstring, snmp, sockets, ssl  


*больше нет необходимости в использовании прокси(возможность работы через прокси убрана с версии 2.0)*



### Подключение к вашему проекту
```
composer install meklis/switcher-core
```

### Как использовать
```PHP
<?php
require __DIR__ . "/vendor/autoload.php";

use SwitcherCore\Modules\Helper; 
use SwitcherCore\Switcher\CoreConnector; //Подготавливает объект кора, более удобно
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\PhpCache; //Кеш для кора, или используйте свой, реализовав интерфейс SwitcherCore\Switcher\CacheInterface
 
 $deviceIp = '127.0.0.1';
 $deviceCommunity = 'public';
 $deviceLogin = 'login';
 $devicePassword = 'password';
 
$coreConnector = new CoreConnector(
    //Возвращает путь к встроенному каталогу с конфигурацией
    //При желании можно скопировать конфигурацию с библиотеки (vendor/meklis/switcher-core/configs) и изменять ее
    Helper::getBuildInConfig()    
);
$connector = ($coreConnector)
    //Кеш устанавливать необязательно, но желательно и желательно использовать реализацию с memcache
    ->setCache(new PhpCache());

$core = $connector->init(
    //Метод init возвращает экземпляр класса Device
    Device::init($deviceIp, $deviceCommunity, $deviceLogin, $devicePassword)
        //Установка параметров для подключений, необязательно (в примере указаны дефолтные параметры)
        ->set('consoleConnectionType', Device::CONSOLE_TELNET) 
        ->set('consoleTimeout', 10) 
        ->set('consolePort', 23)
        ->set('snmpRepeats', 3)
        ->set('snmpTimeoutSec', 2)
        ->set('mikrotikApiPort', 8728)
);

//Пример получения данных с модуля 
echo json_encode($core->action('system'), JSON_PRETTY_PRINT);
/*
Модуль system вернет следующий вывод (поля могут изменяться в зависимости от производителя)
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
