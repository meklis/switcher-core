

### arp_info - ARP information (L3 devices)
Name: **arp_info**

**Arguments:**
- **ip**, pattern: __^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$__
- **vlan_id**, pattern: __^[0-9]{1,4}$__
- **mac**, pattern: __^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$__

**Supported devices:**
- Mikrotik RB1100AHx2  __(\SwitcherCore\Modules\RouterOS\ArpInfo)__
- Mikrotik CCR1009-7G-1C-1S+  __(\SwitcherCore\Modules\RouterOS\ArpInfo)__


### arp_ping - ARP ping
Name: **arp_ping**

**Arguments:**
- **ip**, pattern: __^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$__ , required
- **vlan_id**, pattern: __^[0-9]{1,4}$__
- **vlan_name**, pattern: __^[0-9a-zA-Z_\-]{1,}$__
- **count**, pattern: __^[0-9]{1,}$__

**Supported devices:**
- Mikrotik RB1100AHx2  __(\SwitcherCore\Modules\RouterOS\ArpPing)__
- Mikrotik CCR1009-7G-1C-1S+  __(\SwitcherCore\Modules\RouterOS\ArpPing)__


### cable_diag - Cable diagnostic
Name: **cable_diag**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\CableDiag\Des3526Parser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkDgs1100Parser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkDgs1100Parser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\CableDiag\DlinkParser)__


### clear_counters - Clear counters
Name: **clear_counters**

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)__


### counters - Counters on port
Name: **counters**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Counters\DefaultParser)__


### ctrl_port_descr - Port description configuration
Name: **ctrl_port_descr**

**Arguments:**
- **port**, pattern: __^[0-9]{1,4}$__ , required
- **description**, pattern: __^[0-9a-zA-Z_]{1,}$__ , required

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)__


### ctrl_port_speed - Port speed configuration
Name: **ctrl_port_speed**

**Arguments:**
- **port**, pattern: __^[0-9]{1,4}$__ , required
- **speed**, pattern: __^auto|(10|100|1000|10000)-(Half|Full)$__ , required

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)__


### ctrl_port_state - Port state configuration
Name: **ctrl_port_state**

**Arguments:**
- **port**, pattern: __^[0-9]{1,4}$__ , required
- **state**, pattern: __^(disable|enable)$__ , required

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)__


### ctrl_vlan_port - Vlan configuration on port
Name: **ctrl_vlan_port**

**Arguments:**
- **id**, pattern: __^[0-9]{1,4}$__ , required
- **port**, pattern: __^[0-9]{1,4}$__ , required
- **type**, pattern: __^(tagged|untagged)$__
- **action**, pattern: __^(delete|add)$__ , required

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)__


### ctrl_vlan_state - Vlan configuration on device
Name: **ctrl_vlan_state**

**Arguments:**
- **id**, pattern: __^[0-9]{1,4}$__
- **name**, pattern: __^[0-9a-zA-Z_]{1,16}$__
- **action**, pattern: __^(delete|create)$__ , required

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)__


### errors - Errors on port
Name: **errors**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Errors\DefaultParser)__


### fdb - MAC forwarding database
Name: **fdb**

**Arguments:**
- **port**, pattern: __.*__
- **mac**, pattern: __.*__
- **vlan_id**, pattern: __^[0-9]{1,4}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Fdb\DefaultParser)__
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\ZTE\Fdb)__


### interface_vlan_info - Interface information (vlans on L3 devices)
Name: **interface_vlan_info**

**Arguments:**
- **name**, pattern: __^[0-9a-zA-Z_]{1,16}$__
- **vlan_id**, pattern: __^[0-9]{1,4}$__

**Supported devices:**
- Mikrotik RB1100AHx2  __(\SwitcherCore\Modules\RouterOS\InterfaceVlanInfo)__
- Mikrotik CCR1009-7G-1C-1S+  __(\SwitcherCore\Modules\RouterOS\InterfaceVlanInfo)__


### link_info - Port information
Name: **link_info**

**Arguments:**
- **port**, pattern: __^.*$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Link\DlinkDes3526Parser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1100Parser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1100Parser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Link\DlinkDgs1210Parser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Link\DlinkParser)__
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\ZTE\LinkInfo)__


### ont_info - ONT info (on PON)
Name: **ont_info**

**Arguments:**
- **port**, pattern: __.*__

**Supported devices:**
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\ZTE\OntInfo)__


### pvid - PVID table
Name: **pvid**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Vlan\PvidParser)__


### reboot - Reboot device
Name: **reboot**

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\Reboot)__


### rmon - RMON statistic
Name: **rmon**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Rmon\DefaultParser)__


### save_config - Save configuration
Name: **save_config**

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3028  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3052  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DES-3526  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)__


### sfp_info - SFP into
Name: **sfp_info**

**Arguments:**
- **port**, pattern: __.*__

**Supported devices:**
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\ZTE\SfpLinkInfo)__


### slot_info - Slot information (ZTE devices)
Name: **slot_info**

**Arguments:**
- **slot_num**, pattern: __^[0-9]{1,4}$__

**Supported devices:**
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\ZTE\SlotInfo)__


### system - System information
Name: **system**

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- Mikrotik RB1100AHx2  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- Mikrotik CCR1009-7G-1C-1S+  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__
- ZTE ZXPON C300  __(\SwitcherCore\Modules\Snmp\System\DefaultParser)__


### vlans - Vlan information
Name: **vlans**

**Arguments:**
- **vlan_id**, pattern: __^[0-9]{1,4}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Vlan\DlinkVlanParser)__


### vlans_by_port - Vlan information over port
Name: **vlans_by_port**

**Arguments:**
- **port**, pattern: __^[0-9]{1,3}$__

**Supported devices:**
- D-link DES-1228/ME  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3028  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3052  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3028G  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-10/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-10/C1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-18/C1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-26/C1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-28/C1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-28F/C1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-18/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-26/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-28/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3200-28F/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DES-3526  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1100-06/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1100-10/ME  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-28/ME/A2  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-20/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-10/ME/A1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-20/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-28/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-1210-28XS/ME/B1  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__
- D-link DGS-3000-26TC  __(\SwitcherCore\Modules\Snmp\Vlan\VlanByPorts)__