### Список поддерживаемых модулей    
    
### [address_list_ctrl](#address_list_ctrl) - Управление записями в адрес-листе 
    
**Аргументы:**    
- **_id**, проверка выражением: *.**    
- **action**, проверка выражением: *^(remove|add|disable|enable)$*, обязательный    
- **name**, проверка выражением: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **comment**, проверка выражением: *.**    
- **timeout**, проверка выражением: *.**    
      
    
    
### [address_list_info](#address_list_info) - Информация по адрес-листам (Router OS) 
    
**Аргументы:**    
- **name**, проверка выражением: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
      
    
    
### [arp_info](#arp_info) - ARP таблица 
    
**Аргументы:**    
- **ip**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
- **mac**, проверка выражением: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **status**, проверка выражением: *^(disabled|invalid|OK)$*    
      
    
    
### [arp_ping](#arp_ping) - ARP ping 
    
**Аргументы:**    
- **ip**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*, обязательный    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
- **count**, проверка выражением: *^[0-9]{1,}$*    
      
    
    
### [cable_diag](#cable_diag) - Диагностика кабеля (длина и состояние пары) 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "port": 2,
        "pairs": [
            {
                "number": 1,
                "status": "Open",
                "length": "29"
            },
            {
                "number": 2,
                "status": "Open",
                "length": "29"
            }
        ]
    },
    {
        "port": 4,
        "pairs": [
            {
                "number": 1,
                "status": "Open",
                "length": "14"
            },
            {
                "number": 2,
                "status": "Open",
                "length": "13"
            }
        ]
    },
    {
        "port": 10,
        "pairs": [
            {
                "number": 1,
                "status": "Open",
                "length": "19"
            },
            {
                "number": 2,
                "status": "Open",
                "length": "19"
            }
        ]
    },
    {
        "port": 13,
        "pairs": [
            {
                "number": 1,
                "status": "Open",
                "length": "14"
            },
            {
                "number": 2,
                "status": "Open",
                "length": "14"
            }
        ]
    },
    {
        "port": 24,
        "pairs": [
            {
                "number": 1,
                "status": "NoCable",
                "length": "0"
            },
            {
                "number": 2,
                "status": "NoCable",
                "length": "0"
            }
        ]
    }
]
```             
         
        
</p>
</details>
            
    
### [clear_counters](#clear_counters) - Очистка счетчиков (во всей системе) 
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
true
```             
         
        
</p>
</details>
            
    
### [counters](#counters) - Счетчики на портах 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "hc_in_octets": "52635",
        "port": "1",
        "hc_out_octets": "2068878",
        "hc_out_multicast_pkts": "115",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "2",
        "hc_out_octets": "0",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "48669",
        "port": "3",
        "hc_out_octets": "249964",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "4",
        "hc_out_octets": "0",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "13035",
        "port": "5",
        "hc_out_octets": "27264",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "82",
        "port": "6",
        "hc_out_octets": "9346",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "183665",
        "port": "7",
        "hc_out_octets": "539274",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "256",
        "port": "8",
        "hc_out_octets": "9248",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "13",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "2475",
        "port": "9",
        "hc_out_octets": "16242",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "10",
        "hc_out_octets": "0",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "6704",
        "port": "11",
        "hc_out_octets": "7483",
        "hc_out_multicast_pkts": "116",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "4307",
        "port": "12",
        "hc_out_octets": "3831",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "2",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "13",
        "hc_out_octets": "0",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "14",
        "hc_out_octets": "1884",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "4707",
        "port": "15",
        "hc_out_octets": "4058",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "16",
        "hc_out_octets": "1884",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "12",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "38732",
        "port": "17",
        "hc_out_octets": "161701",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "8",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "495",
        "port": "18",
        "hc_out_octets": "2373",
        "hc_out_multicast_pkts": "14",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "8",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "82",
        "port": "19",
        "hc_out_octets": "2046",
        "hc_out_multicast_pkts": "15",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "8",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "18123",
        "port": "20",
        "hc_out_octets": "242028",
        "hc_out_multicast_pkts": "15",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "8",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "21",
        "hc_out_octets": "1948",
        "hc_out_multicast_pkts": "15",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "8",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "40280",
        "port": "22",
        "hc_out_octets": "1822281",
        "hc_out_multicast_pkts": "15",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "9",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "23",
        "hc_out_octets": "2012",
        "hc_out_multicast_pkts": "15",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "9",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "0",
        "port": "24",
        "hc_out_octets": "0",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "0"
    },
    {
        "hc_in_octets": "5557257",
        "port": "25",
        "hc_out_octets": "350679",
        "hc_out_multicast_pkts": "0",
        "hc_in_multicast_pkts": "14",
        "hc_out_broadcast_pkts": "0",
        "hc_in_broadcast_pkts": "19"
    },
    {
        "hc_in_octets": "61550",
        "port": "26",
        "hc_out_octets": "2737210",
        "hc_out_multicast_pkts": "12",
        "hc_in_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "9",
        "hc_in_broadcast_pkts": "0"
    }
]
```             
         
        
</p>
</details>
            
    
### [ctrl_port_descr](#ctrl_port_descr) - Установка описания порта 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,4}$*, обязательный    
- **description**, проверка выражением: *^[0-9a-zA-Z_]{1,}$*, обязательный    
      
    
    
### [ctrl_port_speed](#ctrl_port_speed) - Установка скорости на порту 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,4}$*, обязательный    
- **speed**, проверка выражением: *^auto|(10|100|1000|10000)-(Half|Full)$*, обязательный    
      
    
    
### [ctrl_port_state](#ctrl_port_state) - Установка административного состояния порта(включение/отключение) 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,4}$*, обязательный    
- **state**, проверка выражением: *^(disable|enable)$*, обязательный    
      
    
    
### [ctrl_static_arp](#ctrl_static_arp) - Управление ARP-ами  (L3 Devices) 
    
**Аргументы:**    
- **action**, проверка выражением: *^(add|remove)$*, обязательный    
- **ip**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
- **mac**, проверка выражением: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **comment**, проверка выражением: *.**    
      
    
    
### [ctrl_static_lease](#ctrl_static_lease) - Управление лизами 
    
**Аргументы:**    
- **action**, проверка выражением: *^(add|remove)$*, обязательный    
- **ip**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
- **mac**, проверка выражением: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **dhcp_server**, проверка выражением: *^.*$*    
- **comment**, проверка выражением: *^.*$*    
      
    
    
### [ctrl_vlan_port](#ctrl_vlan_port) - Управление вланами на порту устройства 
    
**Аргументы:**    
- **id**, проверка выражением: *^[0-9]{1,4}$*, обязательный    
- **port**, проверка выражением: *^[0-9]{1,4}$*, обязательный    
- **type**, проверка выражением: *^(tagged|untagged)$*    
- **action**, проверка выражением: *^(delete|add)$*, обязательный    
      
    
    
### [ctrl_vlan_state](#ctrl_vlan_state) - Управление вланами на устройстве 
    
**Аргументы:**    
- **id**, проверка выражением: *^[0-9]{1,4}$*    
- **name**, проверка выражением: *^[0-9a-zA-Z_]{1,16}$*    
- **action**, проверка выражением: *^(delete|create)$*, обязательный    
      
    
    
### [dhcp_server_info](#dhcp_server_info) - Список DHCP-серверов и их конфиг (RouterOS devices) 
    
**Аргументы:**    
- **name**, проверка выражением: *^.*$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
      
    
    
### [errors](#errors) - Ошибки на портах 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "port": "1",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "2",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "3",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "4",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "5",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "6",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "7",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "8",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "9",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "10",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "11",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "12",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "13",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "14",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "15",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "16",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "17",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "18",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "19",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "20",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "21",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "22",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "23",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "24",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "25",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    },
    {
        "port": "26",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    }
]
```             
         
        
</p>
</details>
            
    
### [fdb](#fdb) - FDB-таблица 
    
**Аргументы:**    
- **port**, проверка выражением: *.**    
- **mac**, проверка выражением: *.**    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "port": "25",
        "vlan_id": "200",
        "mac": "48:8F:5A:8E:3F:DC",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "00:22:15:36:CA:02",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "00:26:22:78:52:02",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "00:AD:24:E6:B3:E7",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "04:5E:A4:7F:73:67",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "04:5E:A4:E2:2B:97",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "04:95:E6:95:08:70",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "04:95:E6:9C:34:98",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "04:BF:6D:05:A8:F5",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "08:60:6E:5F:98:69",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "0C:80:63:28:4D:D3",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "10:7B:EF:5F:91:C9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "10:FE:ED:5C:69:73",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:16:9E:23:FE:D1",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:32:09:D5",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:B1:88:69",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:C2:B4:69",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:C3:0B:C1",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:CA:38:1D",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:CA:44:B1",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:4D:67:CA:7E:19",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:CC:20:D3:4C:11",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "14:CC:20:D7:DF:05",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "18:D6:C7:B7:44:55",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "1C:3B:F3:4F:3E:D4",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "24:A2:E1:E7:0B:B2",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "28:D1:27:B1:A2:55",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "2C:56:DC:CB:5C:F8",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "30:B5:C2:2C:0D:4F",
        "status": "LEARNED"
    },
    {
        "port": "18",
        "vlan_id": "302",
        "mac": "30:B5:C2:74:36:83",
        "status": "LEARNED"
    },
    {
        "port": "16",
        "vlan_id": "302",
        "mac": "3C:84:6A:34:CD:C6",
        "status": "LEARNED"
    },
    {
        "port": "20",
        "vlan_id": "302",
        "mac": "44:33:4C:98:8A:BD",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "48:8F:5A:28:C4:D0",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "48:8F:5A:8E:3F:DC",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:64:2B:1A:5E:1C",
        "status": "LEARNED"
    },
    {
        "port": "1",
        "vlan_id": "302",
        "mac": "50:C7:BF:6B:91:41",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:D2:F5:08:37:51",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:D2:F5:25:95:FF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:D2:F5:27:13:CB",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:D4:F7:DA:8A:2F",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "50:FF:20:4E:99:61",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "58:D5:6E:B1:0E:16",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:48:26:E9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:49:2F:A9",
        "status": "LEARNED"
    },
    {
        "port": "12",
        "vlan_id": "302",
        "mac": "5C:92:5E:49:34:B9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:49:62:51",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:49:84:49",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:49:BC:31",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:52:F0:31",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:1F:89",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:34:49",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:38:69",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:3E:09",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:6F:89",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:9E:59",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:53:C0:11",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "5C:92:5E:6E:12:79",
        "status": "LEARNED"
    },
    {
        "port": "3",
        "vlan_id": "302",
        "mac": "64:66:B3:DE:4D:11",
        "status": "LEARNED"
    },
    {
        "port": "17",
        "vlan_id": "302",
        "mac": "68:FF:7B:6A:30:A0",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "6C:3B:6B:F2:E5:46",
        "status": "LEARNED"
    },
    {
        "port": "5",
        "vlan_id": "302",
        "mac": "74:4D:28:6B:2E:28",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "74:DA:88:2F:8F:E7",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "74:DA:88:74:29:DD",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "74:DA:88:B2:9A:43",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "78:44:76:59:05:D6",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "78:44:76:6A:EA:4D",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "78:44:76:6B:82:E9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "78:44:76:AF:D7:03",
        "status": "LEARNED"
    },
    {
        "port": "9",
        "vlan_id": "302",
        "mac": "7C:8B:CA:D8:02:49",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "84:16:F9:36:11:8B",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "84:16:F9:47:19:F9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "84:16:F9:54:31:DF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "84:16:F9:90:15:39",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "84:16:F9:9D:D3:6B",
        "status": "LEARNED"
    },
    {
        "port": "6",
        "vlan_id": "302",
        "mac": "84:16:F9:D5:32:AF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "90:F6:52:77:B1:99",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "94:0C:6D:AC:42:30",
        "status": "LEARNED"
    },
    {
        "port": "8",
        "vlan_id": "302",
        "mac": "98:DA:C4:2A:1D:97",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "98:DA:C4:C4:0B:E1",
        "status": "LEARNED"
    },
    {
        "port": "7",
        "vlan_id": "302",
        "mac": "98:DA:C4:E3:BA:35",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "98:DE:D0:E4:B1:05",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "A4:2B:B0:D4:0C:27",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "AC:22:0B:BA:42:31",
        "status": "LEARNED"
    },
    {
        "port": "11",
        "vlan_id": "302",
        "mac": "AC:84:C6:BD:2F:B9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "B0:4E:26:19:DC:FB",
        "status": "LEARNED"
    },
    {
        "port": "21",
        "vlan_id": "302",
        "mac": "B0:95:75:50:A6:A3",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "B0:BE:76:87:16:E9",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "B0:BE:76:E1:03:1D",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "B0:BE:76:F5:5D:6B",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C0:4A:00:0C:F3:DF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C4:17:FE:AB:81:5C",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C4:E9:84:59:60:9D",
        "status": "LEARNED"
    },
    {
        "port": "19",
        "vlan_id": "302",
        "mac": "C4:E9:84:FA:E9:3F",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C8:3A:35:05:DA:F8",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C8:3A:35:07:84:70",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C8:3A:35:14:D6:68",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "C8:D7:19:A7:25:07",
        "status": "LEARNED"
    },
    {
        "port": "26",
        "vlan_id": "302",
        "mac": "CC:2D:E0:2C:8C:3F",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "CC:32:E5:84:44:C7",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "CC:32:E5:AE:D0:59",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "D4:6E:0E:50:83:E1",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "D8:0D:17:A6:25:EB",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "D8:0D:17:D9:EF:97",
        "status": "LEARNED"
    },
    {
        "port": "23",
        "vlan_id": "302",
        "mac": "D8:0D:17:F1:6E:26",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "D8:50:E6:A6:17:ED",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "E0:3F:49:3A:BE:D1",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "E0:CB:4E:60:87:9C",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "E4:BE:ED:F2:7F:BC",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "E8:37:7A:91:83:A7",
        "status": "LEARNED"
    },
    {
        "port": "22",
        "vlan_id": "302",
        "mac": "E8:94:F6:4C:8F:8B",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "E8:94:F6:B8:50:55",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:08:6B:C3:61:E7",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:1A:59:42:10:AF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:1A:59:42:10:E3",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:1A:59:42:1D:FF",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:41:18:EB:ED:61",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "EC:41:18:EB:FE:6D",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "F0:DE:F1:B8:B5:4E",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "302",
        "mac": "F4:EC:38:9F:6A:7F",
        "status": "LEARNED"
    },
    {
        "port": "15",
        "vlan_id": "302",
        "mac": "F8:32:E4:45:53:38",
        "status": "LEARNED"
    },
    {
        "port": "0",
        "vlan_id": "502",
        "mac": "1C:BD:B9:70:F4:40",
        "status": "SELF"
    },
    {
        "port": "25",
        "vlan_id": "502",
        "mac": "48:8F:5A:8E:3F:DC",
        "status": "LEARNED"
    },
    {
        "port": "25",
        "vlan_id": "502",
        "mac": "8C:EA:1B:9C:01:EA",
        "status": "LEARNED"
    }
]
```             
         
        
</p>
</details>
            
    
### [interface_vlan_info](#interface_vlan_info) - Информация по интерфейсам (vlans on L3 devices) 
    
**Аргументы:**    
- **name**, проверка выражением: *^[0-9a-zA-Z_]{1,16}$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
      
    
    
### [lease_info](#lease_info) - Lease таблица 
    
**Аргументы:**    
- **ip**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
- **vlan_name**, проверка выражением: *^.*$*    
- **mac**, проверка выражением: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **dhcp_server**, проверка выражением: *^.*$*    
      
    
    
### [link_info](#link_info) - Информация о портах (для свитчей) 
    
**Аргументы:**    
- **port**, проверка выражением: *^.*$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "port": "1",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "1444",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "2",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "1404",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "3",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "4",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "5",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "1404",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "6",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "1404",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "7",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2607",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "8",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2265",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "9",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2752",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "10",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "1425",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "11",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "40543",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "12",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "3858",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "13",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "1425",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "14",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "3391",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "15",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "54739",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "16",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "53716",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "17",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2245",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "18",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "84978",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "19",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2005",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "20",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2240",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "21",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "22",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "3017",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "23",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "2131",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "24",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "53716",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "25",
        "medium_type": "Cooper",
        "type": "GE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    },
    {
        "port": "25",
        "medium_type": "Fiber",
        "type": "GE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "1G-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "26",
        "medium_type": "Cooper",
        "type": "GE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "1404",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    },
    {
        "port": "26",
        "medium_type": "Fiber",
        "type": "GE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Down",
        "description": "1404",
        "admin_state": "Auto",
        "nway_status": "Down",
        "address_learning": "Enabled"
    }
]
```             
         
        
</p>
</details>
            
    
### [onu_reboot](#onu_reboot) - Перезагрузка ОНУ 
    
**Аргументы:**    
- **onu**, проверка выражением: *.**, обязательный    
      
    
    
### [pon_fdb](#pon_fdb) - Returned FDB table on ONTs 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
- **vlan_id**, проверка выражением: *[0-9]{1,4}*    
- **mac**, проверка выражением: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": 16779019,
        "interface": "pon0\/0\/1:11\/1",
        "mac_address": "10:FE:ED:7C:85:F9",
        "vlan_id": 811
    },
    {
        "_id": 16779017,
        "interface": "pon0\/0\/1:9\/1",
        "mac_address": "14:4D:67:99:2F:6D",
        "vlan_id": 811
    },
    {
        "_id": 16779011,
        "interface": "pon0\/0\/1:3\/1",
        "mac_address": "14:4D:67:B1:F0:0D",
        "vlan_id": 811
    },
    {
        "_id": 16779010,
        "interface": "pon0\/0\/1:2\/1",
        "mac_address": "14:4D:67:CA:46:55",
        "vlan_id": 811
    },
    {
        "_id": 16779015,
        "interface": "pon0\/0\/1:7\/1",
        "mac_address": "24:4B:FE:92:0E:E0",
        "vlan_id": 811
    },
    {
        "_id": 16779022,
        "interface": "pon0\/0\/1:14\/1",
        "mac_address": "30:B5:C2:D3:7E:13",
        "vlan_id": 811
    },
    {
        "_id": 16779014,
        "interface": "pon0\/0\/1:6\/1",
        "mac_address": "40:3F:8C:C1:35:77",
        "vlan_id": 811
    },
    {
        "_id": 16779013,
        "interface": "pon0\/0\/1:5\/1",
        "mac_address": "58:D5:6E:BA:C0:3C",
        "vlan_id": 811
    },
    {
        "_id": 16779009,
        "interface": "pon0\/0\/1:1\/1",
        "mac_address": "5C:78:F8:4A:06:26",
        "vlan_id": 811
    },
    {
        "_id": 16779021,
        "interface": "pon0\/0\/1:13\/1",
        "mac_address": "5C:92:5E:49:30:91",
        "vlan_id": 811
    },
    {
        "_id": 16779016,
        "interface": "pon0\/0\/1:8\/1",
        "mac_address": "5C:92:5E:4B:10:CD",
        "vlan_id": 811
    },
    {
        "_id": 16779012,
        "interface": "pon0\/0\/1:4\/1",
        "mac_address": "B0:BE:76:67:2F:9F",
        "vlan_id": 811
    },
    {
        "_id": 16779018,
        "interface": "pon0\/0\/1:10",
        "mac_address": "E0:E8:E6:18:87:83",
        "vlan_id": 46
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_interface_info](#pon_interface_info) - Returned FDB table on ONTs 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "status": "Up",
        "_id": 16779009,
        "interface": "pon0\/0\/1:1\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "120138",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 233,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "99912",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779010,
        "interface": "pon0\/0\/1:2\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "384",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 6,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "10250",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779011,
        "interface": "pon0\/0\/1:3\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "1564",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 19,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "20674",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779012,
        "interface": "pon0\/0\/1:4\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "292",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 4,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "19930",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779013,
        "interface": "pon0\/0\/1:5\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "0",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 0,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "19840",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779014,
        "interface": "pon0\/0\/1:6\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "271496",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 1339,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "9346399",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779015,
        "interface": "pon0\/0\/1:7\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "368",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 5,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "18889",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779016,
        "interface": "pon0\/0\/1:8\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "640",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 10,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "21695",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779017,
        "interface": "pon0\/0\/1:9\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "640",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 10,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "20957",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779019,
        "interface": "pon0\/0\/1:11\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "228",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 3,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "19539",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779020,
        "interface": "pon0\/0\/1:12\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "0",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 0,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "18409",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779021,
        "interface": "pon0\/0\/1:13\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "874",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 13,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "18825",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "status": "Up",
        "_id": 16779022,
        "interface": "pon0\/0\/1:14\/1",
        "admin_status": "Enabled",
        "vlan_id": 811,
        "vlan_mode": "Untagged",
        "stat_in_octets": "131193",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 1156,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "4848874",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "vlan_id": 0,
        "_id": 16779018,
        "interface": "pon0\/0\/1:10\/1",
        "vlan_mode": "Unknown",
        "stat_in_octets": "0",
        "stat_in_undersize_pkts": 0,
        "stat_in_oversize_pkts": 0,
        "stat_in_fragments_pkts": 0,
        "stat_in_crc_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_in_jabber_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_undersize_pkts": 0,
        "stat_out_oversize_pkts": 0,
        "stat_out_fragments_pkts": 0,
        "stat_out_crc_pkts": 0,
        "stat_out_drop_pkts": 0,
        "stat_out_jabber": 0
    },
    {
        "stat_in_octets": "1084819736394",
        "_id": 16777472,
        "interface": "ge0\/0\/1",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 556,
        "stat_out_octets": "74383272803",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "4348565360",
        "_id": 16777728,
        "interface": "ge0\/0\/2",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 10,
        "stat_out_octets": "25105037261",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16777984,
        "interface": "ge0\/0\/3",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16778240,
        "interface": "ge0\/0\/4",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16778496,
        "interface": "xge0\/0\/1",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16778752,
        "interface": "xge0\/0\/2",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "69950460492",
        "_id": 16779008,
        "interface": "pon0\/0\/1",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 54333,
        "stat_out_octets": "1059759837520",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16779264,
        "interface": "pon0\/0\/2",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16779520,
        "interface": "pon0\/0\/3",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    },
    {
        "stat_in_octets": "0",
        "_id": 16779776,
        "interface": "pon0\/0\/4",
        "stat_in_oversize_pkts": 0,
        "stat_in_drop_pkts": 0,
        "stat_out_octets": "0",
        "stat_out_oversize_pkts": 0,
        "stat_out_drop_pkts": 0
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_interfaces_list](#pon_interfaces_list) - Information of PON interfaces 
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "name": "ge0\/0\/1",
        "id": 16777472,
        "xid": 1,
        "type": "1G-SFP"
    },
    {
        "name": "ge0\/0\/2",
        "id": 16777728,
        "xid": 2,
        "type": "1G-SFP"
    },
    {
        "name": "ge0\/0\/3",
        "id": 16777984,
        "xid": 3,
        "type": "1G-SFP"
    },
    {
        "name": "ge0\/0\/4",
        "id": 16778240,
        "xid": 4,
        "type": "1G-SFP"
    },
    {
        "name": "xge0\/0\/1",
        "id": 16778240,
        "xid": 5,
        "type": "10G-SFP"
    },
    {
        "name": "xge0\/0\/2",
        "id": 16778752,
        "xid": 6,
        "type": "10G-SFP"
    },
    {
        "name": "pon0\/0\/1",
        "id": 16779008,
        "xid": 7,
        "type": "PON"
    },
    {
        "name": "pon0\/0\/2",
        "id": 16779264,
        "xid": 8,
        "type": "PON"
    },
    {
        "name": "pon0\/0\/3",
        "id": 16779520,
        "xid": 9,
        "type": "PON"
    },
    {
        "name": "pon0\/0\/4",
        "id": 16779776,
        "xid": 10,
        "type": "PON"
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_interfaces_tree](#pon_interfaces_tree) - Information of PON interfaces with onu and parent Ids 
    
**Аргументы:**    
- **as_tree**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: **as_tree**=yes         

Ответ в JSON:          

```json             
[
    {
        "name": "ge0\/0\/1",
        "id": 16777472,
        "parent": null,
        "type": "1G-SFP",
        "status": null
    },
    {
        "name": "ge0\/0\/2",
        "id": 16777728,
        "parent": null,
        "type": "1G-SFP",
        "status": null
    },
    {
        "name": "ge0\/0\/3",
        "id": 16777984,
        "parent": null,
        "type": "1G-SFP",
        "status": null
    },
    {
        "name": "xge0\/0\/1",
        "id": 16778240,
        "parent": null,
        "type": "10G-SFP",
        "status": null
    },
    {
        "name": "xge0\/0\/2",
        "id": 16778752,
        "parent": null,
        "type": "10G-SFP",
        "status": null
    },
    {
        "name": "pon0\/0\/1",
        "id": 16779008,
        "parent": null,
        "type": "PON",
        "status": null,
        "children": [
            {
                "name": "pon0\/0\/1:1",
                "id": 16779009,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:2",
                "id": 16779010,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:3",
                "id": 16779011,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:4",
                "id": 16779012,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:5",
                "id": 16779013,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:6",
                "id": 16779014,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:7",
                "id": 16779015,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:8",
                "id": 16779016,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:9",
                "id": 16779017,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:10",
                "id": 16779018,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:11",
                "id": 16779019,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:12",
                "id": 16779020,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:13",
                "id": 16779021,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            },
            {
                "name": "pon0\/0\/1:14",
                "id": 16779022,
                "parent": 16779008,
                "type": "ONT",
                "status": "Online"
            }
        ]
    },
    {
        "name": "pon0\/0\/2",
        "id": 16779264,
        "parent": null,
        "type": "PON",
        "status": null
    },
    {
        "name": "pon0\/0\/3",
        "id": 16779520,
        "parent": null,
        "type": "PON",
        "status": null
    },
    {
        "name": "pon0\/0\/4",
        "id": 16779776,
        "parent": null,
        "type": "PON",
        "status": null
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_ont_clear_counters](#pon_ont_clear_counters) - Clear counters on ONT (uni port) 
    
**Аргументы:**    
- **interface**, проверка выражением: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)\/?([0-9]{1,3})?$*, обязательный    
      
    
    
### [pon_ont_delete](#pon_ont_delete) - Delete ont from system 
    
**Аргументы:**    
- **interface**, проверка выражением: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, обязательный    
      
    
    
### [pon_ont_reboot](#pon_ont_reboot) - Reboot ONU by interface 
    
**Аргументы:**    
- **interface**, проверка выражением: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, обязательный    
      
    
    
### [pon_ont_reset](#pon_ont_reset) - Reset ONT configuration 
    
**Аргументы:**    
- **interface**, проверка выражением: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, обязательный    
      
    
    
### [pon_onts_general_info](#pon_onts_general_info) - Returned ONTs MAC addresses 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": 16779009,
        "interface": "pon0\/0\/1:1",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779010,
        "interface": "pon0\/0\/1:2",
        "ver_software": "V2.1.5",
        "ver_hardware": "HZ660.2A",
        "vendor": "xPON",
        "model": "101Z"
    },
    {
        "_id": 16779011,
        "interface": "pon0\/0\/1:3",
        "ver_software": "V2.1.3",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779012,
        "interface": "pon0\/0\/1:4",
        "ver_software": "V2.1.3",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779013,
        "interface": "pon0\/0\/1:5",
        "ver_software": "V2.1.3",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779014,
        "interface": "pon0\/0\/1:6",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779015,
        "interface": "pon0\/0\/1:7",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779016,
        "interface": "pon0\/0\/1:8",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779017,
        "interface": "pon0\/0\/1:9",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779018,
        "interface": "pon0\/0\/1:10",
        "ver_software": "V2.1.12",
        "ver_hardware": "R310.1A",
        "vendor": "HWTC",
        "model": "15BR"
    },
    {
        "_id": 16779019,
        "interface": "pon0\/0\/1:11",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779020,
        "interface": "pon0\/0\/1:12",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779021,
        "interface": "pon0\/0\/1:13",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    },
    {
        "_id": 16779022,
        "interface": "pon0\/0\/1:14",
        "ver_software": "V3.0.6",
        "ver_hardware": "HZ660.2A",
        "vendor": "PICO",
        "model": "E710"
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_onts_mac_addr](#pon_onts_mac_addr) - Returned ONTs MAC addresses 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": "16779009",
        "interface": "pon0\/0\/1:1",
        "mac_address": "E0:E8:E6:75:C9:EF"
    },
    {
        "_id": "16779010",
        "interface": "pon0\/0\/1:2",
        "mac_address": "E0:67:B3:BF:8F:E0"
    },
    {
        "_id": "16779011",
        "interface": "pon0\/0\/1:3",
        "mac_address": "E0:67:B3:AE:42:26"
    },
    {
        "_id": "16779012",
        "interface": "pon0\/0\/1:4",
        "mac_address": "E0:67:B3:AD:CC:12"
    },
    {
        "_id": "16779013",
        "interface": "pon0\/0\/1:5",
        "mac_address": "E0:67:B3:AD:CC:00"
    },
    {
        "_id": "16779014",
        "interface": "pon0\/0\/1:6",
        "mac_address": "E0:E8:E6:75:C9:CF"
    },
    {
        "_id": "16779015",
        "interface": "pon0\/0\/1:7",
        "mac_address": "E0:E8:E6:75:C9:B5"
    },
    {
        "_id": "16779016",
        "interface": "pon0\/0\/1:8",
        "mac_address": "E0:E8:E6:75:C9:D5"
    },
    {
        "_id": "16779017",
        "interface": "pon0\/0\/1:9",
        "mac_address": "E0:E8:E6:75:C9:E5"
    },
    {
        "_id": "16779018",
        "interface": "pon0\/0\/1:10",
        "mac_address": "E0:E8:E6:18:87:7D"
    },
    {
        "_id": "16779019",
        "interface": "pon0\/0\/1:11",
        "mac_address": "E0:E8:E6:75:AF:5F"
    },
    {
        "_id": "16779020",
        "interface": "pon0\/0\/1:12",
        "mac_address": "E0:E8:E6:75:AF:41"
    },
    {
        "_id": "16779021",
        "interface": "pon0\/0\/1:13",
        "mac_address": "E0:E8:E6:75:C9:B9"
    },
    {
        "_id": "16779022",
        "interface": "pon0\/0\/1:14",
        "mac_address": "E0:E8:E6:78:9D:DB"
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_onts_optical](#pon_onts_optical) - Returned ONTs MAC addresses 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": 16779009,
        "interface": "pon0\/0\/1:1",
        "rx": -18.12,
        "tx": 1.22,
        "voltage": 3.34,
        "temp": 24.05,
        "distance": 5
    },
    {
        "_id": 16779010,
        "interface": "pon0\/0\/1:2",
        "rx": -21.19,
        "tx": 1.62,
        "voltage": 3.35,
        "temp": 28.46,
        "distance": 5
    },
    {
        "_id": 16779011,
        "interface": "pon0\/0\/1:3",
        "rx": -19.28,
        "tx": 1.81,
        "voltage": 3.29,
        "temp": 28.13,
        "distance": 5
    },
    {
        "_id": 16779012,
        "interface": "pon0\/0\/1:4",
        "rx": -21.8,
        "tx": 1.83,
        "voltage": 3.32,
        "temp": 25.41,
        "distance": 5
    },
    {
        "_id": 16779013,
        "interface": "pon0\/0\/1:5",
        "rx": -22.68,
        "tx": 1.72,
        "voltage": 3.33,
        "temp": 25.41,
        "distance": 5
    },
    {
        "_id": 16779014,
        "interface": "pon0\/0\/1:6",
        "rx": -18.57,
        "tx": 1.34,
        "voltage": 3.32,
        "temp": 26.77,
        "distance": 5
    },
    {
        "_id": 16779015,
        "interface": "pon0\/0\/1:7",
        "rx": -18.6,
        "tx": 1.44,
        "voltage": 3.32,
        "temp": 30.16,
        "distance": 5
    },
    {
        "_id": 16779016,
        "interface": "pon0\/0\/1:8",
        "rx": -19.17,
        "tx": 1.37,
        "voltage": 3.33,
        "temp": 27.45,
        "distance": 5
    },
    {
        "_id": 16779017,
        "interface": "pon0\/0\/1:9",
        "rx": -19.63,
        "tx": 1.49,
        "voltage": 3.33,
        "temp": 24.73,
        "distance": 5
    },
    {
        "_id": 16779018,
        "interface": "pon0\/0\/1:10",
        "rx": -9.65,
        "tx": 1.7,
        "voltage": 3.34,
        "temp": 35.92,
        "distance": 6
    },
    {
        "_id": 16779019,
        "interface": "pon0\/0\/1:11",
        "rx": -17.88,
        "tx": 1.34,
        "voltage": 3.32,
        "temp": 28.13,
        "distance": 5
    },
    {
        "_id": 16779020,
        "interface": "pon0\/0\/1:12",
        "rx": -17.88,
        "tx": 1.5,
        "voltage": 3.35,
        "temp": 29.14,
        "distance": 5
    },
    {
        "_id": 16779021,
        "interface": "pon0\/0\/1:13",
        "rx": -17.98,
        "tx": 1.46,
        "voltage": 3.34,
        "temp": 25.07,
        "distance": 5
    },
    {
        "_id": 16779022,
        "interface": "pon0\/0\/1:14",
        "rx": -21.55,
        "tx": 1.43,
        "voltage": 3.35,
        "temp": 23.38,
        "distance": 5
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_onts_status](#pon_onts_status) - Returned onts statuses 
    
**Аргументы:**    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": 16779009,
        "interface": "pon0\/0\/1:1",
        "status": "Online"
    },
    {
        "_id": 16779010,
        "interface": "pon0\/0\/1:2",
        "status": "Online"
    },
    {
        "_id": 16779011,
        "interface": "pon0\/0\/1:3",
        "status": "Online"
    },
    {
        "_id": 16779012,
        "interface": "pon0\/0\/1:4",
        "status": "Online"
    },
    {
        "_id": 16779013,
        "interface": "pon0\/0\/1:5",
        "status": "Online"
    },
    {
        "_id": 16779014,
        "interface": "pon0\/0\/1:6",
        "status": "Online"
    },
    {
        "_id": 16779015,
        "interface": "pon0\/0\/1:7",
        "status": "Online"
    },
    {
        "_id": 16779016,
        "interface": "pon0\/0\/1:8",
        "status": "Online"
    },
    {
        "_id": 16779017,
        "interface": "pon0\/0\/1:9",
        "status": "Online"
    },
    {
        "_id": 16779018,
        "interface": "pon0\/0\/1:10",
        "status": "Online"
    },
    {
        "_id": 16779019,
        "interface": "pon0\/0\/1:11",
        "status": "Online"
    },
    {
        "_id": 16779020,
        "interface": "pon0\/0\/1:12",
        "status": "Online"
    },
    {
        "_id": 16779021,
        "interface": "pon0\/0\/1:13",
        "status": "Online"
    },
    {
        "_id": 16779022,
        "interface": "pon0\/0\/1:14",
        "status": "Online"
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_onts_status_detailed](#pon_onts_status_detailed) - Returned ONTs MAC addresses 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, проверка выражением: *yes|no*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "_id": 16779009,
        "interface": "pon0\/0\/1:1",
        "status": "Online",
        "last_reg": 1611725158,
        "last_reg_since": "9d 8h 39min 14sec",
        "last_down_reason": "Losi",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779010,
        "interface": "pon0\/0\/1:2",
        "status": "Online",
        "last_reg": 1612519577,
        "last_reg_since": "0d 3h 58min 55sec",
        "last_down_reason": "Losi",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779011,
        "interface": "pon0\/0\/1:3",
        "status": "Online",
        "last_reg": 1611879801,
        "last_reg_since": "7d 13h 41min 51sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779012,
        "interface": "pon0\/0\/1:4",
        "status": "Online",
        "last_reg": 1611725165,
        "last_reg_since": "9d 8h 39min 7sec",
        "last_down_reason": "Losi",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779013,
        "interface": "pon0\/0\/1:5",
        "status": "Online",
        "last_reg": 1611725168,
        "last_reg_since": "9d 8h 39min 4sec",
        "last_down_reason": "Losi",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779014,
        "interface": "pon0\/0\/1:6",
        "status": "Online",
        "last_reg": 1612267124,
        "last_reg_since": "3d 2h 6min 28sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779015,
        "interface": "pon0\/0\/1:7",
        "status": "Online",
        "last_reg": 1612267289,
        "last_reg_since": "3d 2h 3min 43sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779016,
        "interface": "pon0\/0\/1:8",
        "status": "Online",
        "last_reg": 1612507990,
        "last_reg_since": "0d 7h 12min 2sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779017,
        "interface": "pon0\/0\/1:9",
        "status": "Online",
        "last_reg": 1612267135,
        "last_reg_since": "3d 2h 6min 17sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779018,
        "interface": "pon0\/0\/1:10",
        "status": "Online",
        "last_reg": 1612518851,
        "last_reg_since": "0d 4h 11min 1sec",
        "last_down_reason": "Losi",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779019,
        "interface": "pon0\/0\/1:11",
        "status": "Online",
        "last_reg": 1612007141,
        "last_reg_since": "6d 2h 19min 31sec",
        "last_down_reason": "",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779020,
        "interface": "pon0\/0\/1:12",
        "status": "Online",
        "last_reg": 1612012256,
        "last_reg_since": "6d 0h 54min 16sec",
        "last_down_reason": "",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779021,
        "interface": "pon0\/0\/1:13",
        "status": "Online",
        "last_reg": 1612021602,
        "last_reg_since": "5d 22h 18min 30sec",
        "last_down_reason": "Dying Gasp",
        "admin_status": "Enabled"
    },
    {
        "_id": 16779022,
        "interface": "pon0\/0\/1:14",
        "status": "Online",
        "last_reg": 1612167410,
        "last_reg_since": "4d 5h 48min 22sec",
        "last_down_reason": "",
        "admin_status": "Enabled"
    }
]
```             
         
        
</p>
</details>
            
    
### [pon_registered_onts](#pon_registered_onts) - Count registered onts on pon 
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "interface": "pon0\/0\/1",
        "_id": 16779008,
        "_interface": {
            "name": "pon0\/0\/1",
            "id": 16779008,
            "xid": 7,
            "type": "PON",
            "onu_num": null,
            "onu_id": null,
            "uni": null
        },
        "count": "14"
    },
    {
        "interface": "pon0\/0\/2",
        "_id": 16779264,
        "_interface": {
            "name": "pon0\/0\/2",
            "id": 16779264,
            "xid": 8,
            "type": "PON",
            "onu_num": null,
            "onu_id": null,
            "uni": null
        },
        "count": "0"
    },
    {
        "interface": "pon0\/0\/3",
        "_id": 16779520,
        "_interface": {
            "name": "pon0\/0\/3",
            "id": 16779520,
            "xid": 9,
            "type": "PON",
            "onu_num": null,
            "onu_id": null,
            "uni": null
        },
        "count": "0"
    },
    {
        "interface": "pon0\/0\/4",
        "_id": 16779776,
        "_interface": {
            "name": "pon0\/0\/4",
            "id": 16779776,
            "xid": 10,
            "type": "PON",
            "onu_num": null,
            "onu_id": null,
            "uni": null
        },
        "count": "0"
    }
]
```             
         
        
</p>
</details>
            
    
### [pvid](#pvid) - PVID таблица 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "pvid": "302",
        "port": "1"
    },
    {
        "pvid": "302",
        "port": "2"
    },
    {
        "pvid": "302",
        "port": "3"
    },
    {
        "pvid": "302",
        "port": "4"
    },
    {
        "pvid": "302",
        "port": "5"
    },
    {
        "pvid": "302",
        "port": "6"
    },
    {
        "pvid": "302",
        "port": "7"
    },
    {
        "pvid": "302",
        "port": "8"
    },
    {
        "pvid": "302",
        "port": "9"
    },
    {
        "pvid": "302",
        "port": "10"
    },
    {
        "pvid": "302",
        "port": "11"
    },
    {
        "pvid": "302",
        "port": "12"
    },
    {
        "pvid": "302",
        "port": "13"
    },
    {
        "pvid": "302",
        "port": "14"
    },
    {
        "pvid": "302",
        "port": "15"
    },
    {
        "pvid": "302",
        "port": "16"
    },
    {
        "pvid": "302",
        "port": "17"
    },
    {
        "pvid": "302",
        "port": "18"
    },
    {
        "pvid": "302",
        "port": "19"
    },
    {
        "pvid": "302",
        "port": "20"
    },
    {
        "pvid": "302",
        "port": "21"
    },
    {
        "pvid": "302",
        "port": "22"
    },
    {
        "pvid": "302",
        "port": "23"
    },
    {
        "pvid": "302",
        "port": "24"
    },
    {
        "pvid": "1",
        "port": "25"
    },
    {
        "pvid": "302",
        "port": "26"
    }
]
```             
         
        
</p>
</details>
            
    
### [reboot](#reboot) - Перезагрузка устройства 
      
    
    
### [rmon](#rmon) - RMON статистика (более детальная инфа о ошибках) 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "ether_stats_crc_align_errors": "0",
        "port": "1",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "2",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "3",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "4",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "5",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "6",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "7",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "8",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "9",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "10",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "11",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "12",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "13",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "14",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "15",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "16",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "17",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "18",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "19",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "20",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "21",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "5",
        "port": "22",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "5",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "23",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "24",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "25",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "26",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "27",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    },
    {
        "ether_stats_crc_align_errors": "0",
        "port": "28",
        "ether_stats_undersize_pkts": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0",
        "ether_stats_collisions": "0",
        "ether_stats_drop_events": "0"
    }
]
```             
         
        
</p>
</details>
            
    
### [save_config](#save_config) - Сохранение конфигурации 
      
    
    
### [sfp_info](#sfp_info) - Информация о SFP-модулях 
    
**Аргументы:**    
- **port**, проверка выражением: *.**    
      
    
    
### [simple_queue_ctrl](#simple_queue_ctrl) - Управление ограничением скорости 
    
**Аргументы:**    
- **_id**, проверка выражением: *.**    
- **action**, проверка выражением: *^(remove|add|disable|enable)$*, обязательный    
- **name**, проверка выражением: *.**    
- **target**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, проверка выражением: *.**    
- **limit-at**, проверка выражением: *.**    
- **max-limit**, проверка выражением: *.**    
- **parent**, проверка выражением: *.**    
- **comment**, проверка выражением: *.**    
      
    
    
### [simple_queue_info](#simple_queue_info) - Информация о ограничении скорости  (микротик) 
    
**Аргументы:**    
- **_id**, проверка выражением: *.**    
- **name**, проверка выражением: *.**    
- **target**, проверка выражением: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, проверка выражением: *.**    
- **parent**, проверка выражением: *.**    
      
    
    
### [slot_info](#slot_info) - Информация о слотах (ZTE devices) 
    
**Аргументы:**    
- **slot_num**, проверка выражением: *^[0-9]{1,4}$*    
      
    
    
### [system](#system) - Системная информация о устройстве 
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
{
    "descr": "EasyPath Ethernet-PON",
    "uptime": "936d 23h 23min 20sec",
    "contact": "contact",
    "name": "EasyPath Series PON Switch Access",
    "location": "location",
    "mac_addr": "E0:67:B3:7A:34:90",
    "vendor_name": "C-Data",
    "serial_num": "AF2703-1906000003",
    "board_software_ver": "V1.4.1_190422",
    "board_hardware_ver": "V2.0",
    "meta": {
        "name": "C-Data FD1204",
        "detect": {
            "description": "EasyPath Ethernet-PON",
            "objid": "^.1.3.6.1.4.1.17409$"
        },
        "ports": 0,
        "extra": {
            "telnet_conn_type": "ios",
            "device_type": "pon",
            "interfaces": [
                {
                    "name": "ge0\/0\/1",
                    "id": 16777472,
                    "xid": 1,
                    "type": "1G-SFP"
                },
                {
                    "name": "ge0\/0\/2",
                    "id": 16777728,
                    "xid": 2,
                    "type": "1G-SFP"
                },
                {
                    "name": "ge0\/0\/3",
                    "id": 16777984,
                    "xid": 3,
                    "type": "1G-SFP"
                },
                {
                    "name": "ge0\/0\/4",
                    "id": 16778240,
                    "xid": 4,
                    "type": "1G-SFP"
                },
                {
                    "name": "xge0\/0\/1",
                    "id": 16778240,
                    "xid": 5,
                    "type": "10G-SFP"
                },
                {
                    "name": "xge0\/0\/2",
                    "id": 16778752,
                    "xid": 6,
                    "type": "10G-SFP"
                },
                {
                    "name": "pon0\/0\/1",
                    "id": 16779008,
                    "xid": 7,
                    "type": "PON"
                },
                {
                    "name": "pon0\/0\/2",
                    "id": 16779264,
                    "xid": 8,
                    "type": "PON"
                },
                {
                    "name": "pon0\/0\/3",
                    "id": 16779520,
                    "xid": 9,
                    "type": "PON"
                },
                {
                    "name": "pon0\/0\/4",
                    "id": 16779776,
                    "xid": 10,
                    "type": "PON"
                }
            ]
        },
        "modules": [
            "system",
            "vlans",
            "pon_interfaces_list",
            "pon_registered_onts",
            "pon_onts_status",
            "pon_onts_mac_addr",
            "pon_onts_optical",
            "pon_onts_status_detailed",
            "pon_onts_general_info",
            "pon_fdb",
            "pon_interface_info",
            "pon_interfaces_tree",
            "save_config",
            "pon_ont_reboot",
            "pon_ont_reset",
            "pon_ont_delete",
            "pon_ont_clear_counters"
        ]
    }
}
```             
         
        
</p>
</details>
            
    
### [vlans](#vlans) - Информация о вланах на устройстве 
    
**Аргументы:**    
- **vlan_id**, проверка выражением: *^[0-9]{1,4}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "id": "1",
        "name": "vlan1",
        "ports": {
            "tagged": [],
            "untagged": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "ge0\/0\/3",
                "ge0\/0\/4",
                "xge0\/0\/1",
                "xge0\/0\/2",
                "pon0\/0\/1",
                "pon0\/0\/2",
                "pon0\/0\/3",
                "pon0\/0\/4"
            ],
            "forbidden": [],
            "egress": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "ge0\/0\/3",
                "ge0\/0\/4",
                "xge0\/0\/1",
                "xge0\/0\/2",
                "pon0\/0\/1",
                "pon0\/0\/2",
                "pon0\/0\/3",
                "pon0\/0\/4"
            ]
        }
    },
    {
        "id": "810",
        "name": "vlan810",
        "ports": {
            "tagged": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "pon0\/0\/1"
            ],
            "untagged": [],
            "forbidden": [],
            "egress": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "pon0\/0\/1"
            ]
        }
    },
    {
        "id": "811",
        "name": "vlan811",
        "ports": {
            "tagged": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "pon0\/0\/1"
            ],
            "untagged": [],
            "forbidden": [],
            "egress": [
                "ge0\/0\/1",
                "ge0\/0\/2",
                "pon0\/0\/1"
            ]
        }
    }
]
```             
         
        
</p>
</details>
            
    
### [vlans_by_port](#vlans_by_port) - Информация о вланах на портах 
    
**Аргументы:**    
- **port**, проверка выражением: *^[0-9]{1,3}$*    
      
<details>
<summary>Пример ответа</summary>
<p>
Параметры запроса: без параметров         

Ответ в JSON:          

```json             
[
    {
        "port": "1",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "2",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "3",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "4",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "5",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "6",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "7",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "8",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "9",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "10",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "11",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "12",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "13",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "14",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "15",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "16",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "17",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "18",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "19",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "20",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "21",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "22",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "23",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "24",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "25",
        "untagged": [],
        "tagged": [
            {
                "name": "J1ext302",
                "id": "302"
            },
            {
                "name": "J1sw502",
                "id": "502"
            }
        ],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            },
            {
                "name": "J1sw502",
                "id": "502"
            }
        ],
        "forbidden": []
    },
    {
        "port": "26",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "27",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    },
    {
        "port": "28",
        "untagged": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "tagged": [],
        "egress": [
            {
                "name": "J1ext302",
                "id": "302"
            }
        ],
        "forbidden": []
    }
]
```             
         
        
</p>
</details>
            
    
### [zte_card_list](#zte_card_list) - Listing of cards on OLT 
      
    
    
### [zte_fdb](#zte_fdb) - FDB таблица с интерфейса/порта/ОНУ 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*    
- **interface**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*    
      
    
    
### [zte_gpon_onu_profile_list](#zte_gpon_onu_profile_list) - List ONU profiles for GPON 
    
**Аргументы:**    
- **type**, проверка выражением: *^(remote|line)$*, обязательный    
      
    
    
### [zte_onu_dereg](#zte_onu_dereg) - Allow send configuration command to interface 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, обязательный    
      
    
    
### [zte_onu_ether_iface_info](#zte_onu_ether_iface_info) - Инфо о Ethernet портах на ONU (UNI ports) 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, обязательный    
      
    
    
### [zte_onu_info](#zte_onu_info) - Информация о ОНУшке (детально) 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, обязательный    
      
    
    
### [zte_onu_interface_console](#zte_onu_interface_console) - Allow send configuration command to interface 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, обязательный    
- **command**, проверка выражением: *.**, обязательный    
      
    
    
### [zte_onu_pon_info](#zte_onu_pon_info) - Информация о всех онушках в порту PON 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, обязательный    
      
    
    
### [zte_onu_registration_epon](#zte_onu_registration_epon) - ONU registration for GPON 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, обязательный    
- **type**, проверка выражением: *.**, обязательный    
- **mac**, проверка выражением: *.**, обязательный    
- **number**, проверка выражением: *[0-9]{1,3}*, обязательный    
      
    
    
### [zte_onu_registration_gpon](#zte_onu_registration_gpon) - ONU registration for GPON 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, обязательный    
- **type**, проверка выражением: *.**, обязательный    
- **serial**, проверка выражением: *.**, обязательный    
- **profile_line**, проверка выражением: *.**, обязательный    
- **profile_remote**, проверка выражением: *.**, обязательный    
- **number**, проверка выражением: *[0-9]{1,3}*, обязательный    
      
    
    
### [zte_onu_signal_strength](#zte_onu_signal_strength) - Инфо у уровне сигналов ОНУ 
    
**Аргументы:**    
- **onu**, проверка выражением: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, обязательный    
      
    
    
### [zte_onu_state_by_interface](#zte_onu_state_by_interface) - List ONU state by interface 
    
**Аргументы:**    
- **interface**, проверка выражением: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, обязательный    
- **parse**, проверка выражением: *.**    
      
    
    
### [zte_unregistered_onu](#zte_unregistered_onu) - List unregistered ONU 
    
**Аргументы:**    
- **type**, проверка выражением: *^(all|gpon|epon)$*    
      
