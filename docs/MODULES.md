
### arp_info - ARP information (L3 devices)
Name: **arp_info**

**Arguments:**
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^.*$*
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*
- **status**, pattern: *^(disabled|invalid|OK)$*

**Response example**
``` 
$parameters=json_decode('{"ip":"185.190.150.65"}', true);
$core->action('arp_info', $parameters);

[
    {
        "ip": "185.190.150.65",
        "mac": "64:D1:54:EE:A7:69",
        "dynamic": "true",
        "comment": "",
        "vlan_id": -1,
        "status": "disabled",
        "extra": {
            "id": "*169",
            "interface_name": "ether1"
        }
    }
]
```

**Supported devices:**
- All mikrotik with routerOS 

### arp_ping - ARP ping
Name: **arp_ping**

**Arguments:**
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*, required
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*
- **count**, pattern: *^[0-9]{1,}$*

``` 
$parameters=json_decode('{"ip":"185.190.150.65","vlan_name":"ether1"}', true);
$core->action('arp_ping', $parameters);

[
    {
        "seq": "0",
        "host": "64:D1:54:EE:A7:69",
        "time": "0ms",
        "sent": "1",
        "received": "1",
        "packet-loss": "0",
        "min-rtt": "0ms",
        "avg-rtt": "0ms",
        "max-rtt": "0ms"
    }
]
```

**Supported devices:**
- All mikrotik with routerOS 

### cable_diag - Cable diagnostic
Name: **cable_diag**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

**Response example(test script)**
```
$parameters=json_decode('{"port":"1"}', true);
$core->action('cable_diag', $parameters);

[
    {
        "port": 1,
        "pairs": [
            {
                "number": 1,
                "status": "OK",
                "length": "71"
            },
            {
                "number": 2,
                "status": "OK",
                "length": "71"
            }
        ]
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\CableDiag\Des3526Parser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkDgs1100Parser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkDgs1100Parser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\CableDiag\DlinkParser)*


### clear_counters - Clear counters
Name: **clear_counters**

**Response example(test script)**
``` 
$parameters=json_decode('[]', true);
$core->action('clear_counters', $parameters);

true
```

**Supported devices:**
- D-Link devices 

### counters - Counters on port
Name: **counters**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

**Response example**
``` 
$parameters=json_decode('{"port":"25"}', true);
$core->action('counters', $parameters);

[
    {
        "hc_out_octets": "260561",
        "port": "25",
        "hc_out_multicast_pkts": "0",
        "hc_out_broadcast_pkts": "1",
        "hc_in_octets": "7376986",
        "hc_in_broadcast_pkts": "169",
        "hc_in_multicast_pkts": "22"
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Counters\DefaultParser)*


### ctrl_port_descr - Port description configuration
Name: **ctrl_port_descr**

**Arguments:**
- **port**, pattern: *^[0-9]{1,4}$*, required
- **description**, pattern: *^[0-9a-zA-Z_]{1,}$*, required

**Response example**
``` 
$parameters=json_decode('{"port":"3","description":"test"}', true);
$core->action('ctrl_port_descr', $parameters);

true
```

**Supported devices:**
- D-Link devices 


### ctrl_port_speed - Port speed configuration
Name: **ctrl_port_speed**

**Arguments:**
- **port**, pattern: *^[0-9]{1,4}$*, required
- **speed**, pattern: *^auto|(10|100|1000|10000)-(Half|Full)$*, required

**Response example**
``` 
$parameters=json_decode('{"port":"3","speed":"auto"}', true);
$core->action('ctrl_port_speed', $parameters);

true
```

**Supported devices:**
- D-Link devices 


### ctrl_port_state - Port state configuration
Name: **ctrl_port_state**

**Arguments:**
- **port**, pattern: *^[0-9]{1,4}$*, required
- **state**, pattern: *^(disable|enable)$*, required

**Response example**
``` 
$parameters=json_decode('{"port":"4","state":"enable"}', true);
$core->action('ctrl_port_state', $parameters);

true
```


**Supported devices:**
- D-Link devices 


### ctrl_static_arp - Adding and removing static ARP (L3 Devices)
Name: **ctrl_static_arp**

**Arguments:**
- **action**, pattern: *^(add|remove)$*, required
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*
- **comment**, pattern: *.**

**Response example**
``` 

```

**Supported devices:**
- All mikrotik with routerOS 


### ctrl_static_lease - Control static leases
Name: **ctrl_static_lease**

**Arguments:**
- **action**, pattern: *^(add|remove)$*, required
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^.*$*
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*
- **dhcp_server**, pattern: *^.*$*
- **comment**, pattern: *^.*$*
 

**Supported devices:**
- All mikrotik with routerOS 

### ctrl_vlan_port - Vlan configuration on port
Name: **ctrl_vlan_port**

**Arguments:**
- **id**, pattern: *^[0-9]{1,4}$*, required
- **port**, pattern: *^[0-9]{1,4}$*, required
- **type**, pattern: *^(tagged|untagged)$*
- **action**, pattern: *^(delete|add)$*, required

**Response example**
``` 
$parameters=json_decode('{"id":"400","port":"4","type":"tagged","action":"add"}', true);
$core->action('ctrl_vlan_port', $parameters);
true

$parameters=json_decode('{"id":"400","port":"4","action":"delete"}', true);
$core->action('ctrl_vlan_port', $parameters);
true
```

**Supported devices:**
- D-Link devices 


### ctrl_vlan_state - Vlan configuration on device
Name: **ctrl_vlan_state**

**Arguments:**
- **id**, pattern: *^[0-9]{1,4}$*
- **name**, pattern: *^[0-9a-zA-Z_]{1,16}$*
- **action**, pattern: *^(delete|create)$*, required

**Response example**
``` 
$parameters=json_decode('{"id":"400","name":"TEST","action":"create"}', true);
$core->action('ctrl_vlan_state', $parameters);

true
```

**Supported devices:**
- D-Link devices 

### dhcp_server_info - DHCP-server information (RouterOS devices)
Name: **dhcp_server_info**

**Arguments:**
- **name**, pattern: *^.*$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^.*$*

**Response example**
``` 
$parameters=json_decode('{"name":"dhcp3"}', true);
$core->action('dhcp_server_info', $parameters);

[
    {
        "name": "dhcp3",
        "interface": "bridge_wifi",
        "lease_time": "10m",
        "address_pool": "dhcp_pool5",
        "extra": {
            "vlan": null
        }
    }
]
```

**Supported devices:**
- All mikrotik with routerOS 

### errors - Errors on port
Name: **errors**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

**Response example**
``` 
$parameters=json_decode('{"port":"4"}', true);
$core->action('errors', $parameters);

[
    {
        "port": "4",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Errors\DefaultParser)*


### fdb - MAC forwarding database
Name: **fdb**

**Arguments:**
- **port**, pattern: *.**
- **mac**, pattern: *.**
- **vlan_id**, pattern: *^[0-9]{1,4}$*

**Response example**
``` 
$parameters=json_decode('{"port":"9"}', true);
$core->action('fdb', $parameters);

[
    {
        "port": "9",
        "vlan_id": "406",
        "mac": "00:1B:38:2C:97:D1",
        "status": "LEARNED"
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Fdb\DefaultParser)*
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Dlink\ZTE\Fdb)*


### interface_vlan_info - Interface information (vlans on L3 devices)
Name: **interface_vlan_info**

**Arguments:**
- **name**, pattern: *^[0-9a-zA-Z_]{1,16}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*

**Response example**
``` 
$parameters=json_decode('{"name":"mng"}', true);
$core->action('interface_vlan_info', $parameters);

[
    {
        "vlan_id": "1000",
        "name": "mng",
        "disabled": "true",
        "arp": "enabled"
    }
]
```

**Supported devices:**
- All mikrotik with routerOS 

### lease_info - Lease information
Name: **lease_info**

**Arguments:**
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^.*$*
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*
- **dhcp_server**, pattern: *^.*$*

**Response example**
``` 
$parameters=json_decode('{"ip":"30.30.0.8"}', true);
$core->action('lease_info', $parameters);

[
    {
        "ip": "30.30.0.8",
        "mac": "00:0B:82:E3:3A:44",
        "status": "bound",
        "expires_at": 1572980866,
        "server": "dhcp2",
        "extra": {
            "id": "*12AC",
            "client_id": "1:0:b:82:e3:3a:44",
            "server": {
                "name": "dhcp2",
                "interface": "bridge_office",
                "lease_time": "10m",
                "address_pool": "dhcp_pool4",
                "extra": {
                    "vlan": null
                }
            }
        }
    }
]
```

**Supported devices:**
- All mikrotik with routerOS 


### link_info - Port information
Name: **link_info**

**Arguments:**
- **port**, pattern: *^.*$*

**Response example**
``` 
$parameters=json_decode('{"port":"9"}', true);
$core->action('link_info', $parameters);

[
    {
        "port": "9",
        "medium_type": "Cooper",
        "type": "FE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "",
        "admin_state": "Auto",
        "nway_status": "100-Full",
        "address_learning": "Enabled"
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Link\DlinkDes3526Parser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1100Parser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1100Parser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Link\DlinkDgs1210Parser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Link\DlinkParser)*
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Dlink\ZTE\LinkInfo)*

 
### pvid - PVID table
Name: **pvid**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

**Response example**
``` 
$parameters=json_decode('{"port":"9"}', true);
$core->action('pvid', $parameters);

[
    {
        "pvid": "406",
        "port": "9"
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Vlan\PvidParser)*


### reboot - Reboot device
Name: **reboot**


**Supported devices:**
- D-Link devices 


### rmon - RMON statistic
Name: **rmon**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*
 
**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Rmon\DefaultParser)*


### save_config - Save configuration
Name: **save_config**

**Response example**
``` 

```

**Supported devices:**
- D-Link devices 


### system - System information
Name: **system**

**Response example**
``` 
$parameters=json_decode('[]', true);
$core->action('system', $parameters);

{
    "descr": "D-Link DES-3200-26 Fast Ethernet Switch",
    "uptime": "4d 16h 11min 5sec",
    "contact": "asusgrin@gmail.com",
    "name": "sw-eurocity-J1b-3p-1",
    "location": "Krykovshina",
    "meta": {
        "name": "D-link DES-3200-26\/A1",
        "detect": {
            "description": "^D-Link DES-3200-26 Fast Ethernet Switch$",
            "objid": "^.*113.1.5$"
        },
        "ports": 26,
        "extra": {
            "diag_linkup": true,
            "telnet_conn_type": "dlink"
        },
        "modules": [
            "fdb",
            "link_info",
            "counters",
            "system",
            "vlans",
            "cable_diag",
            "errors",
            "rmon",
            "pvid",
            "clear_counters",
            "save_config",
            "reboot",
            "vlans_by_port",
            "ctrl_port_state",
            "ctrl_port_speed",
            "ctrl_port_descr",
            "ctrl_vlan_state",
            "ctrl_vlan_port"
        ]
    }
}
```

**Supported devices:**
- D-Link devices 
- Mikrotik devices

### vlans - Vlan information
Name: **vlans**

**Arguments:**
- **vlan_id**, pattern: *^[0-9]{1,4}$*

**Response example**
``` 
$parameters=json_decode('{"vlan_id":"400"}', true);
$core->action('vlans', $parameters);

[
    {
        "name": "TEST",
        "id": "400",
        "ports": {
            "egress": [
                "4"
            ],
            "untagged": [],
            "forbidden": [],
            "tagged": [
                "4"
            ]
        }
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Vlan\DlinkVlanParser)*


### vlans_by_port - Vlan information over port
Name: **vlans_by_port**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

**Response example**
``` 
$parameters=json_decode('{"port":"4"}', true);
$core->action('vlans_by_port', $parameters);

[
    {
        "port": "4",
        "untagged": [
            {
                "name": "fake50",
                "id": "50"
            }
        ],
        "tagged": [
            {
                "name": "TEST",
                "id": "400"
            }
        ],
        "egress": [
            {
                "name": "fake50",
                "id": "50"
            },
            {
                "name": "TEST",
                "id": "400"
            }
        ],
        "forbidden": []
    }
]
```

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3028  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3052  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DES-3526  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Dlink\Vlan\VlanByPorts)*


### simple_queue_ctrl - Control simple queue
Name: **simple_queue_ctrl**   
    
**Arguments:**    
- **_id**, pattern: *.**    
- **action**, pattern: *^(remove|add|disable|enable)$*, required    
- **name**, pattern: *.**    
- **target**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, pattern: *.**  (Variables  - /queue type print)
- **limit-at**, pattern: *.**    , example: 50M/50M (In/Out = 50Mbits)   
- **max-limit**, pattern: *.**, example: 50M/50M (In/Out = 50Mbits)   
- **parent**, pattern: *.**    
- **comment**, pattern: *.**    

**Supported devices:**    
- Mikrotik RouterOS  *(\SwitcherCore\Modules\RouterOS\SimpleQueuesCtrl)*   

**Work example**
``` 
$parameters=json_decode('{"action":"add","name":"TEST","target":"10.10.10.12","max-limit":"50M/50M","comment":"TEST"}', true);
$core->action('simple_queue_ctrl', $parameters);

Response: 
"*419"
```    
       
### simple_queue_info - Info of simple queue
Name: **simple_queue_info**   
    
**Arguments:**    
- **_id**, pattern: *.**    
- **name**, pattern: *.**    
- **target**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, pattern: *.**    
- **parent**, pattern: *.**    

**Supported devices:**    
- Mikrotik RouterOS  *(\SwitcherCore\Modules\RouterOS\SimpleQueuesInfo)*   

**Work example**
``` 
$parameters=json_decode('{"target":"10.10.10.11\/32"}', true);
$core->action('simple_queue_info', $parameters);
[
    {
        "_id": "*41B",
        "name": "10.10.10.11",
        "target": "10.10.10.11\/32",
        "parent": "none",
        "type": "default-small\/default-small",
        "limit-at": "0\/0",
        "max-limit": "20480000\/20480000", #Max limit in bytes or k|M|G
        "disabled": false,
        "dynamic": false,
        "comment": "sync_queue"
    }
]
```

    
### address_list_ctrl - Working with address list
Name: **address_list_ctrl**   
    
**Arguments:**    
- **_id**, pattern: *.**    
- **action**, pattern: *^(remove|add|disable|enable)$*, required    
- **name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **comment**, pattern: *.**    
- **timeout**, pattern: *.**    

**Supported devices:**    
- Mikrotik RouterOS  *(\SwitcherCore\Modules\RouterOS\AddressListControl)*   

**Work example**
```
$parameters=json_decode('{"action":"add","name":"TEST_LIST","address":"10.0.0.1","comment":"TEST_COMMENT"}', true);
$core->action('address_list_ctrl', $parameters);

"*5E359F"

$parameters=json_decode('{"_id":"*5E359F","action":"remove"}', true);
$core->action('address_list_ctrl', $parameters);

{
    "*5E359F": true
}
```
    
### address_list_info - Info by addresses list
Name: **address_list_info**   
    
**Arguments:**    
- **name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    

**Supported devices:**    
- Mikrotik RouterOS  *(\SwitcherCore\Modules\RouterOS\AddressListInfo)*   

**Work example**
```
$parameters=json_decode('{"name":"TEST"}', true);
$core->action('address_list_info', $parameters);

[
    {
        "_id": "*5E35A8",
        "name": "TEST",
        "address": "1.1.1.1",
        "created": "2020-01-29 16:47:49",
        "dynamic": false,
        "disabled": false
    }
]

```



### zte_card_list - Listing of cards on OLT
Name: **zte_card_list**

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\CardList)*

```json
[
    {
        "rack": "1",
        "shelf": "1",
        "slot": "1",
        "cfg_type": "ETGO",
        "real_type": "ETGOD",
        "port": "8",
        "hard_ver": "V1.0.0",
        "soft_ver": "V2.1.0"
    },
    {
        "rack": "1",
        "shelf": "1",
        "slot": "2",
        "cfg_type": "GTGO",
        "real_type": "GTGOG",
        "port": "8",
        "hard_ver": "V1.0.0",
        "soft_ver": "V2.1.0"
    },
    {
        "rack": "1",
        "shelf": "1",
        "slot": "3",
        "cfg_type": "PRAM",
        "real_type": "PRAM",
        "port": "3",
        "hard_ver": "V1.0.0",
        "soft_ver": "V1.01"
    },
    {
        "rack": "1",
        "shelf": "1",
        "slot": "4",
        "cfg_type": "SMXA",
        "real_type": "SMXA",
        "port": "3",
        "hard_ver": "V1.0.0",
        "soft_ver": "V2.1.0"
    }
]
```
### zte_fdb - ONU signal strength info (on PON)
Name: **zte_fdb**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*

```json
[
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "00:30:67:17:CE:9B",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "10:47:80:0E:EA:6F",
        "vlan_id": 4056,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:B3:90",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:44",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "44"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "B0:BE:76:42:42:4F",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:9A:70",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:12",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "12"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "14:CC:20:AD:CA:0B",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "30:B5:C2:3C:89:27",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "C4:6E:1F:E1:AB:0B",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "E8:94:F6:2C:AF:39",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "80:FB:06:6C:70:AF",
        "vlan_id": 4056,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "30:B5:C2:3C:84:03",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "34:79:16:B7:20:35",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "64:66:B3:36:0D:11",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "B0:BE:76:42:40:99",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "F4:F2:6D:B1:A7:F5",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:EF:84",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:33",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "33"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:BF:8A",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:15",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "15"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "C4:6E:1F:AF:CA:95",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "B8:A3:86:9B:32:97",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "E4:77:23:F1:EB:4A",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:17",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "17"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:BE:4C",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:23",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "23"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "30:B5:C2:34:70:F7",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "C0:4A:00:AA:45:F3",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:EA:7A",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:28",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "28"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:D8:EC",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:35",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "35"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "14:CC:20:2A:5A:E9",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:EC:A8",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:22",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "22"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:EE:C4",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:19",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "19"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D4:DA:5C",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:27",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "27"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:9B:48",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:4",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "4"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "B0:BE:76:42:42:ED",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:BF:C0",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:24",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "24"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "A4:B6:1E:32:BC:B1",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:ED:BC",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:18",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "18"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:D6:16",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:2",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "2"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "80:FB:06:6C:70:BA",
        "vlan_id": 4056,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "BC:EE:7B:1F:3E:30",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:1",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "1"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:A7:7E",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:6",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "6"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D6:DD:5A",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:47",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "47"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "64:66:B3:36:8D:A1",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:48",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "48"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "B0:BE:76:88:91:49",
        "vlan_id": 4055,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:49",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "49"
        }
    },
    {
        "vport": "1",
        "time": "N\/A",
        "mac": "DC:02:8E:D3:A7:42",
        "vlan_id": 4078,
        "type": "Dynamic",
        "onu": "gpon-onu_1\/2\/1:30",
        "_interface": {
            "technology": "gpon",
            "is_onu": true,
            "is_port": false,
            "shelf": "1",
            "slot": "2",
            "port": "1",
            "onu": "30"
        }
    }
]
```

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\Fdb)*


### zte_gpon_onu_profile_list - List ONU profiles for GPON
Name: **zte_gpon_onu_profile_list**

**Arguments:**
- **type**, pattern: *^(remote|line)$*, required

```php
$parameters=json_decode('{"type":"remote"}', true);
$core->action('zte_gpon_onu_profile_list', $parameters);
```
```json

[
    "One\/VID\/4078",
    "ZTE\/VID\/4078",
    "1GE_Trunk_4055\/4056\/4078",
    "VLAN_4052\/4055\/4056\/4060\/4078"
]
```

```json5
$parameters=json_decode('{"type":"line"}', true);
$core->action('zte_gpon_onu_profile_list', $parameters);
[
    "100mb",
    "500mb",
    "1000mb"
]
```

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\GponOntProfileList)*


### zte_onu_dereg - Allow send configuration command to interface
Name: **zte_onu_dereg**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\DeregOnt)*


### zte_onu_ether_iface_info - ONU info (on PON)
Name: **zte_onu_ether_iface_info**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OnuEtherPortInfo)*


### zte_onu_info - ONU info (on PON)
Name: **zte_onu_info**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OntInfo)*


### zte_onu_interface_console - Allow send configuration command to interface
Name: **zte_onu_interface_console**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required
- **command**, pattern: *.**, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OntInterfaceConfCommand)*


### zte_onu_pon_info - Info about all ONUs on interface
Name: **zte_onu_pon_info**

**Arguments:**
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required



### zte_onu_registration_epon - ONU registration for GPON
Name: **zte_onu_registration_epon**

**Arguments:**
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required
- **type**, pattern: *.**, required
- **mac**, pattern: *.**, required
- **number**, pattern: *[0-9]{1,3}*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OntRegistrationEPON)*


### zte_onu_registration_gpon - ONU registration for GPON
Name: **zte_onu_registration_gpon**

**Arguments:**
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required
- **type**, pattern: *.**, required
- **serial**, pattern: *.**, required
- **profile_line**, pattern: *.**, required
- **profile_remote**, pattern: *.**, required
- **number**, pattern: *[0-9]{1,3}*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OntRegistrationGPON)*


### zte_onu_signal_strength - ONU signal strength info (on PON)
Name: **zte_onu_signal_strength**

**Arguments:**
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OnuSignalStrengthInfo)*


### zte_onu_state_by_interface - List ONU state by interface
Name: **zte_onu_state_by_interface**

**Arguments:**
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required
- **parse**, pattern: *.**

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\OntStateInfo)*


### zte_unregistered_onu - List unregistered ONU
Name: **zte_unregistered_onu**

**Arguments:**
- **type**, pattern: *^(all|gpon|epon)$*

**Supported devices:**
- ZTE ZXPON C320  *(\SwitcherCore\Modules\Telnet\ZTE\C300Series\UnregisteredOntList)*   
