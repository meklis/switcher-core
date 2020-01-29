
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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\CableDiag\Des3526Parser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkDgs1100Parser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkDgs1100Parser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)*
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\ZTE\Fdb)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Link\DlinkDes3526Parser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1100Parser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1100Parser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Link\DlinkParser)*
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\ZTE\LinkInfo)*

 
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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)*


### reboot - Reboot device
Name: **reboot**


**Supported devices:**
- D-Link devices 


### rmon - RMON statistic
Name: **rmon**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*
 
**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)*


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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)*


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