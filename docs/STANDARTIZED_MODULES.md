# Стандартизированные модули

Данные типы модулей предполагают, что аргументы и вывод будет идентичным для всех устройств. Формат вывода прилагается в
данной документации.       
Данные модули могут не отображать полную информацию по конкретному производителю, так как вывод усреднен. Для получения
более полной информации по оборудованию следует использовать другие модули.

## Список модулей

### [interface_list](#interface_list) - Возвращает список интерфейсов устройства

**Аргументы:**

- **interface** [String] - имя интерфейса(name) или его номер(id). В случае, если аргумент передан - будет возвращен
  массив только с одним, указанным интерфейсом или ошибка InterfaceNotFound если интерфейс не был найден
- **parent** [String] - имя родительского интерфейса(name) или его номер(id). В случае, если аргумент передан - будет
  возвращен массив дочерних интерфейсов или ошибка InterfaceNotFound если интерфейс не был найден
- **root** [Boolean(true|false)] - будут возвращены только корневые интерфейсы. Этот параметр имеет значение для
  вложенных интерфесов, например в технологии PON.

!Принимается передача только одного из параметров!

**Возвращаемый результат**       
Array<[интерфесов](#structure_interface)>

Пример json

```json5
[
  {
    "id": 64844524,
    "name": "pon0/1/2:24",
    "parent": 64844500,
    "type": "ONU",
    "meta": {}
  }
] 
```     

### [interface_status](#interface_status) - Возвращает информацию о состоянии интерфейса

**Аргументы:**

- **interface** [String] - имя интерфейса(name) или его номер(id). В случае, если аргумент передан - будет возвращен
  массив только с одним, указанным интерфейсом или ошибка InterfaceNotFound если интерфейс не был найден
- **parent** [String] - имя родительского интерфейса(name) или его номер(id). В случае, если аргумент передан - будет
  возвращен массив дочерних интерфейсов или ошибка InterfaceNotFound если интерфейс не был найден
- **root** [Boolean(true|false)] - будут возвращены только корневые интерфейсы. Этот параметр имеет значение для
  вложенных интерфесов, например в технологии PON.
- **status** [Enum(Up|Down|Disabled)] - вернуть интерфейсы с определенным статусом

!Принимается передача только одного из параметров!

**Возвращаемый результат**       
Array<[интерфесов](#structure_interface)>

Пример json

```json5
[
  {
    "interface": {
      "id": 64844524,
      "name": "pon0/1/2:24",
      "parent": 64844500,
      "type": "ONU",
      "meta": {}
    },
    "status": "Up",
    "address_learning": null,
    "nway_status": null,
    "nway_state": null,
    "last_status_change": 1612518851,
  }
] 
```     


### [system](#system) - Возвращает информацию о системе

**Возвращаемый результат**          
[StructureSystem](#structure_system)

Пример json

```json5
{
  "descr": "C320 Version V2.1.0 Software, Copyright (c) by ZTE Corporation Compiled",
  "uptime": "338d 21h 55min 0sec",
  "contact": "admin",
  "name": "Nivky",
  "location": "Ukraine, Kyiv",
  "meta": {
    "type": "OLT",
    "name": "ZTE ZXPON C320",
    "ports": 0,
    "extra": {
      "telnet_conn_type": "ios"
    },
    "modules": [
      "system",
      "onu_reboot",
      "zte_onu_info",
      "zte_onu_ether_iface_info",
      "zte_fdb",
      "zte_onu_signal_strength",
      "zte_onu_state_by_interface",
      "zte_onu_dereg"
    ]
  }
}
```     

## Структуры

#### [Интерфейс](#structure_interface)

**Interface**

```json5
{
  "id": 64844524,
  //[Integer] Внутренний ID интерфейса для устройства, должен быть уникальным
  "name": "pon0/1/2:24",
  //[String] Имя интерфейса. Имя может отличаться в зависимости от производителя и модели устройства
  "parent": 64844500,
  //[Integer|null] ID родительского интерфейса, если такой есть. В случае если это корневой интерфейс - возвращается null
  "type": "ONU",
  //[Enum[String]] Тип интерфейса* 
  "meta": {}
  //[null|Array[String]Interface] Возвращаемые дополнительные данные по интерфейсу**. Может быть статус, детальный разбор парсинга интерфейса или любая другая информация
}
```

#### [Состояние интерфейса](#structure_interface_status)

**InterfaceStatus**

```json5
  {
  "interface": {
    Interface
  },
  //Интерфейс 
  "status": "Up",
  //[Enum(Up|Down|Disabled)] Состояние. Для ONU - Up=Online|Down=Offline
  "address_learning": null,
  //[Boolean|Null] Изучать MAC-адреса
  "nway_status": null,
  //[Enum(*)] Текущая скорость интерфейса  
  "nway_state": null,
  //[Enum(*)] Административная скорость интерфейса 
  "last_status_change": 1612518851,
  //[Integer]  Последнее изменение состояние 
  "meta": {}
  // Метаданные 
}
```

Возможные status:
- Up - интерфейс работает/ОНУшка в сети
- Down - интерфейс не работает/ОНУшка не в сети
- Disabled - интерфейс отключен
      
Возможные nway_status|nway_state:     
- 10-Half
- 100-Half
- 1G-Half
- 10-Full
- 100-Full
- 1G-Half
- 1G-Full
- 10G-Full
- 40G-Full
- Down (только для nway_status)
- Disabled - (только для nway_state)

**Метаданные могут быть любые и любого формата или могут быть null     
     
     
     
#### [Система](#structure_system)

**System**

```json5
{
  "descr": "C320 Version V2.1.0 Software, Copyright (c) by ZTE Corporation Compiled", //Описание возвращаемое устройством
  "uptime": "338d 21h 55min 0sec", //Аптайм 
  "contact": "admin", //Контакт возвращаемый устройством 
  "name": "Nivky", //Имя возвращаемое устройством  
  "location": "Ukraine, Kyiv", //Размещение, возвращаемое устройством 
  "meta": { //Метаинформация с switcher-core 
    "type": "OLT", //Тип устройства. Возможные варианты: SWITCH|OLT|ROUTER
    "name": "ZTE ZXPON C320", //Имя устройства в конфиге switcher-core
    "extra": {}, //Дополнительная информация по устройству. Указывается в конфиге модели, должен быть массив ключ=>значение
    "modules": [ //Список поддерживаемых модулей
      "system",
      "onu_reboot",
      "zte_onu_info",
      "zte_onu_ether_iface_info",
      "zte_fdb"
    ]
  }
}
```
