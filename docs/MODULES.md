# Build-in Modules
### Module SwitcherCore\Modules\Snmp\Fdb\DefaultParser
```
        json_encode($core->action('fdb', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "port": "27",
        "vlan_id": "453",
        "mac": "20:89:84:2C:CA:3A",
        "status": "LEARNED"
    },
    {
        "port": "27",
        "vlan_id": "453",
        "mac": "4C:CC:6A:D5:81:93",
        "status": "LEARNED"
    },
    {
        "port": "27",
        "vlan_id": "453",
        "mac": "B0:BE:76:1B:49:54",
        "status": "LEARNED"
    }
]
```

### Module SwitcherCore\Modules\Snmp\Link\DlinkParser
```
        json_encode($core->action('link_info', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "port": "27",
        "medium_type": "Cooper",
        "type": "GE",
        "last_change": null,
        "connector_present": null,
        "oper_status": "Up",
        "description": "3369516",
        "admin_state": "Auto",
        "nway_status": "1G-Full",
        "address_learning": "Enabled"
    }
]
```

### Module SwitcherCore\Modules\Snmp\Counters\DefaultParser
```
        json_encode($core->action('counters', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "hc_out_octets": "25261552791",
        "port": "27",
        "hc_out_broadcast_pkts": "6311150",
        "hc_out_multicast_pkts": "10596",
        "hc_in_multicast_pkts": "20992",
        "hc_in_broadcast_pkts": "2068",
        "hc_in_octets": "27111678291"
    }
]
```

### Module SwitcherCore\Modules\Snmp\System\DefaultParser
```
        json_encode($core->action('system', ['port'=>3]), JSON_PRETTY_PRINT);
{
    "descr": "D-Link DES-3028 Fast Ethernet Switch",
    "uptime": "0d 12h 50min 47sec",
    "contact": "",
    "name": "Kiev-Borshchagovskij (493\/5013)",
    "location": "Zodchikh, 6a(9)",
    "firmware": "2.94.B21",
    "revision": "A1",
    "meta": {
        "name": "D-link DES-3028",
        "detect": {
            "description": "^D-Link DES-3028 Fast Ethernet Switch",
            "objid": ".*"
        },
        "ports": 28,
        "extra": {
            "diag_ports": [
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
                27,
                28
            ],
            "diag_linkup": false
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
            "vlans_by_port"
        ]
    }
}
```

### Module SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser
```
        json_encode($core->action('vlans', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "name": "default",
        "id": "1",
        "ports": {
            "egress": [],
            "untagged": [],
            "forbidden": []
        }
    },
    {
        "name": "switches430",
        "id": "430",
        "ports": {
            "egress": [
                "26"
            ],
            "untagged": [],
            "forbidden": [],
            "tagged": [
                "26"
            ]
        }
    },
    {
        "name": "INTERNET",
        "id": "453",
        "ports": {
            "egress": [
                "1",
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9",
                "10",
                "11",
                "12",
                "13",
                "14",
                "15",
                "16",
                "17",
                "18",
                "19",
                "20",
                "21",
                "22",
                "23",
                "24",
                "25",
                "26",
                "27",
                "28"
            ],
            "untagged": [
                "1",
                "2",
                "3",
                "4",
                "5",
                "6",
                "7",
                "8",
                "9",
                "10",
                "11",
                "12",
                "13",
                "14",
                "15",
                "16",
                "17",
                "18",
                "19",
                "20",
                "21",
                "22",
                "23",
                "24",
                "25",
                "27",
                "28"
            ],
            "forbidden": [],
            "tagged": [
                "26"
            ]
        }
    }
]
```

### Module SwitcherCore\Modules\Snmp\CableDiag\DlinkParser
```
        json_encode($core->action('cable_diag', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "port": 27,
        "pairs": [
            {
                "number": 1,
                "status": "OK",
                "length": "33"
            },
            {
                "number": 2,
                "status": "OK",
                "length": "33"
            },
            {
                "number": 3,
                "status": "OK",
                "length": "33"
            },
            {
                "number": 4,
                "status": "OK",
                "length": "33"
            }
        ]
    }
]
```

### Module SwitcherCore\Modules\Snmp\Errors\DefaultParser
```
        json_encode($core->action('errors', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "port": "27",
        "in_errors": "0",
        "out_errors": "0",
        "in_discards": "0",
        "out_discards": "0"
    }
]
```

### Module SwitcherCore\Modules\Snmp\Rmon\DefaultParser
```
        json_encode($core->action('rmon', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "ether_stats_undersize_pkts": "0",
        "port": "27",
        "ether_stats_drop_events": "0",
        "ether_stats_collisions": "0",
        "ether_stats_crc_align_errors": "0",
        "ether_stats_oversize_pkts": "0",
        "ether_stats_fragments": "0",
        "ether_stats_jabber": "0"
    }
]
```

### Module SwitcherCore\Modules\Snmp\Vlan\PvidParser
```
        json_encode($core->action('pvid', ['port'=>3]), JSON_PRETTY_PRINT);
[
    {
        "pvid": "453",
        "port": "27"
    }
]
```

### Module SwitcherCore\Modules\Snmp\Vlan\VlanByPorts
```
        json_encode($core->action('rmon', ['port'=>3]), JSON_PRETTY_PRINT);
[
   {
       "port": "1",
       "untagged": [
           {
               "name": "INTERNET",
               "id": "453"
           }
       ],
       "tagged": [],
       "egress": [
           {
               "name": "INTERNET",
               "id": "453"
           }
       ],
       "forbidden": []
   }
]
```