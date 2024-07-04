# Switcher-Core
### Библиотека PHP для работы с сетевым оборудованием 

## Обзор
Библиотека позволяет работать с устройствами через единый интерфейс - нет необходимости писать интерфейсы и врапперы под каждого вендора, используйте модули.
Набор модулей и возвращаемый результат может отличаться в зависимости от типа оборудования, но это необходимая жертва. 

Так же есть возможность доработки своих модулей, на случай, если вашего оборудования не будет в списке - просто сделайте fork). 

### Поддерживаемые интерфейсы 
* Telnet
* SSH 
* SNMP(v2c only)
* RouterOS API(without SSL)

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
- [x] Cisco switches (базово)
- [ ] Cisco routers
- [x] GCOM OLTs
- [x] Alcatel switches (базово)
- [x] Eltex switches (базово)
- [x] HP switches (базово)
- [x] Dell switches (базово)
- [x] Allied Telesis (базово)
- [x] TP-link (базово)
- [x] Juniper switches (базово)
- [x] Raisecom switches (базово)

### [Полный список поддерживаемого оборудования и их модулей](docs/DEVICES.md)     
### [Список модулей](docs/MODULES.md)    

### Необходимо для начала работы   
PHP >= 7.2    
Модули PHP: yaml, zip, curl, json, mbstring, snmp, sockets, ssl  


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


### Разработка
#### Основные файлы и каталоги
* **configs/oids/** - содержит yaml файлы списков оидов snmp (пути к файлам указываются в модели устройства)
* **configs/modules.yml** - список модулей (с описанием аргументов)
* **configs/models/** - список моделей устройств
* **src/Modules/** - реализация модулей 


#### Описание файла из configs/models(на примере Edge-core ECS4120-28F)
```yaml
- name: Edge-core ECS4120-28F  
  key: edgecore_ecs4120_28f # ключ модели(он должен быть уникален для всей системы)
  ports: 26 # количество портов(необязательный параметр)
  device_type: SWITCH # тип устройства(необязательный параметр)
  inputs: # используемые интерфейсы работы с оборудованием (важный параметр. может быть еще console, routeros_api).
    - snmp
  detect: {description: ^ECS4120-28F,  objid: .1.3.6.1.4.1.259.10.1.45.103 } # параметры опредления устройства
  oids: # список оидов, которые нужно добавить
    - ./oids/edgecore/ecs4120.yml
  modules: #Имя модуля - его реализация
    system: \SwitcherCore\Modules\Edgecore\System
    parse_interface: \SwitcherCore\Modules\Edgecore\ParseInterface
    interfaces_list: \SwitcherCore\Modules\Edgecore\InterfacesList
    fdb: \SwitcherCore\Modules\Edgecore\Fdb
    link_info: \SwitcherCore\Modules\Edgecore\LinkInfo
    counters: \SwitcherCore\Modules\Edgecore\Counters
    vlans: \SwitcherCore\Modules\Edgecore\VlansDot1q
    vlans_by_port: \SwitcherCore\Modules\Edgecore\VlanByPorts
    errors: \SwitcherCore\Modules\Edgecore\Errors
    interface_descriptions: \SwitcherCore\Modules\Edgecore\Descriptions
    sys_resources: \SwitcherCore\Modules\Edgecore\SysResources
    rmon: \SwitcherCore\Modules\Edgecore\Rmon
    pvid: \SwitcherCore\Modules\Edgecore\PvidDot1q
    sfp_media: \SwitcherCore\Modules\Edgecore\SfpMediaInfo
    sfp_optical: \SwitcherCore\Modules\Edgecore\SfpOpticalInfo
    sfp_diag: \SwitcherCore\Modules\Edgecore\SfpDiag
```


### Используется в [wildcore.tools](https://wildcore.tools)
