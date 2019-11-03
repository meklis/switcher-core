

### arp_info - ARP information (L3 devices)
Name: **arp_info**

**Arguments:**
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*

**Supported devices:**
- Mikrotik RB1100AHx2  *(\SwitcherCore\Modules\RouterOS\ArpInfo)*
- Mikrotik CCR1009-7G-1C-1S+  *(\SwitcherCore\Modules\RouterOS\ArpInfo)*


### arp_ping - ARP ping
Name: **arp_ping**

**Arguments:**
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*, required
- **vlan_id**, pattern: *^[0-9]{1,4}$*
- **vlan_name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*
- **count**, pattern: *^[0-9]{1,}$*

**Supported devices:**
- Mikrotik RB1100AHx2  *(\SwitcherCore\Modules\RouterOS\ArpPing)*
- Mikrotik CCR1009-7G-1C-1S+  *(\SwitcherCore\Modules\RouterOS\ArpPing)*


### cable_diag - Cable diagnostic
Name: **cable_diag**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

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

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\ClearCounters)*


### counters - Counters on port
Name: **counters**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

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

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\DescriptionPortControl)*


### ctrl_port_speed - Port speed configuration
Name: **ctrl_port_speed**

**Arguments:**
- **port**, pattern: *^[0-9]{1,4}$*, required
- **speed**, pattern: *^auto|(10|100|1000|10000)-(Half|Full)$*, required

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\SpeedPortControl)*


### ctrl_port_state - Port state configuration
Name: **ctrl_port_state**

**Arguments:**
- **port**, pattern: *^[0-9]{1,4}$*, required
- **state**, pattern: *^(disable|enable)$*, required

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\StatePortControl)*


### ctrl_vlan_port - Vlan configuration on port
Name: **ctrl_vlan_port**

**Arguments:**
- **id**, pattern: *^[0-9]{1,4}$*, required
- **port**, pattern: *^[0-9]{1,4}$*, required
- **type**, pattern: *^(tagged|untagged)$*
- **action**, pattern: *^(delete|add)$*, required

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\VlanPortControl)*


### ctrl_vlan_state - Vlan configuration on device
Name: **ctrl_vlan_state**

**Arguments:**
- **id**, pattern: *^[0-9]{1,4}$*
- **name**, pattern: *^[0-9a-zA-Z_]{1,16}$*
- **action**, pattern: *^(delete|create)$*, required

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\VlanStateControl)*


### errors - Errors on port
Name: **errors**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

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

**Supported devices:**
- Mikrotik RB1100AHx2  *(\SwitcherCore\Modules\RouterOS\InterfaceVlanInfo)*
- Mikrotik CCR1009-7G-1C-1S+  *(\SwitcherCore\Modules\RouterOS\InterfaceVlanInfo)*


### link_info - Port information
Name: **link_info**

**Arguments:**
- **port**, pattern: *^.*$*

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


### ont_info - ONT info (on PON)
Name: **ont_info**

**Arguments:**
- **port**, pattern: *.**

**Supported devices:**
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\ZTE\OntInfo)*


### pvid - PVID table
Name: **pvid**

**Arguments:**
- **port**, pattern: *^[0-9]{1,3}$*

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
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\Reboot)*


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

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3028  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3052  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DES-3526  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Telnet\Dlink\SaveConfig)*


### sfp_info - SFP into
Name: **sfp_info**

**Arguments:**
- **port**, pattern: *.**

**Supported devices:**
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\ZTE\SfpLinkInfo)*


### slot_info - Slot information (ZTE devices)
Name: **slot_info**

**Arguments:**
- **slot_num**, pattern: *^[0-9]{1,4}$*

**Supported devices:**
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\ZTE\SlotInfo)*


### system - System information
Name: **system**

**Supported devices:**
- D-link DES-1228/ME  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3028  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3052  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3028G  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-10/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-10/C1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-18/C1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-26/C1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-28/C1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-28F/C1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-18/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-26/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-28/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3200-28F/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DES-3526  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1100-06/ME/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1100-10/ME  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-28/ME/A2  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-20/ME/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-10/ME/A1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-20/ME/B1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-28/ME/B1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-1210-28XS/ME/B1  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- D-link DGS-3000-26TC  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- Mikrotik RB1100AHx2  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- Mikrotik CCR1009-7G-1C-1S+  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*
- ZTE ZXPON C300  *(\SwitcherCore\Modules\Snmp\System\DefaultParser)*


### vlans - Vlan information
Name: **vlans**

**Arguments:**
- **vlan_id**, pattern: *^[0-9]{1,4}$*

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