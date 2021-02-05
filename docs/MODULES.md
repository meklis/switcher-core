### Список поддерживаемых модулей    
    
### [address_list_ctrl](#address_list_ctrl) - Working with address list 
    
**Arguments:**    
- **_id**, pattern: *.**    
- **action**, pattern: *^(remove|add|disable|enable)$*, required    
- **name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **comment**, pattern: *.**    
- **timeout**, pattern: *.**    
     
    
### [address_list_info](#address_list_info) - Info by addresses list 
    
**Arguments:**    
- **name**, pattern: *^[0-9a-zA-Z_\-]{1,}$*    
- **address**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
     
    
### [arp_info](#arp_info) - ARP information (L3 devices) 
    
**Arguments:**    
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **status**, pattern: *^(disabled|invalid|OK)$*    
     
    
### [arp_ping](#arp_ping) - ARP ping 
    
**Arguments:**    
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*, required    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
- **count**, pattern: *^[0-9]{1,}$*    
     
    
### [cable_diag](#cable_diag) - Cable diagnostic 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [clear_counters](#clear_counters) - Clear counters 
     
    
### [counters](#counters) - Counters on port 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [ctrl_port_descr](#ctrl_port_descr) - Port description configuration 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,4}$*, required    
- **description**, pattern: *^[0-9a-zA-Z_]{1,}$*, required    
     
    
### [ctrl_port_speed](#ctrl_port_speed) - Port speed configuration 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,4}$*, required    
- **speed**, pattern: *^auto|(10|100|1000|10000)-(Half|Full)$*, required    
     
    
### [ctrl_port_state](#ctrl_port_state) - Port state configuration 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,4}$*, required    
- **state**, pattern: *^(disable|enable)$*, required    
     
    
### [ctrl_static_arp](#ctrl_static_arp) - Adding and removing static ARP (L3 Devices) 
    
**Arguments:**    
- **action**, pattern: *^(add|remove)$*, required    
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **comment**, pattern: *.**    
     
    
### [ctrl_static_lease](#ctrl_static_lease) - Control static leases 
    
**Arguments:**    
- **action**, pattern: *^(add|remove)$*, required    
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **dhcp_server**, pattern: *^.*$*    
- **comment**, pattern: *^.*$*    
     
    
### [ctrl_vlan_port](#ctrl_vlan_port) - Vlan configuration on port 
    
**Arguments:**    
- **id**, pattern: *^[0-9]{1,4}$*, required    
- **port**, pattern: *^[0-9]{1,4}$*, required    
- **type**, pattern: *^(tagged|untagged)$*    
- **action**, pattern: *^(delete|add)$*, required    
     
    
### [ctrl_vlan_state](#ctrl_vlan_state) - Vlan configuration on device 
    
**Arguments:**    
- **id**, pattern: *^[0-9]{1,4}$*    
- **name**, pattern: *^[0-9a-zA-Z_]{1,16}$*    
- **action**, pattern: *^(delete|create)$*, required    
     
    
### [dhcp_server_info](#dhcp_server_info) - DHCP-server information (RouterOS devices) 
    
**Arguments:**    
- **name**, pattern: *^.*$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
     
    
### [errors](#errors) - Errors on port 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [fdb](#fdb) - MAC forwarding database 
    
**Arguments:**    
- **port**, pattern: *.**    
- **mac**, pattern: *.**    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
     
    
### [interface_vlan_info](#interface_vlan_info) - Interface information (vlans on L3 devices) 
    
**Arguments:**    
- **name**, pattern: *^[0-9a-zA-Z_]{1,16}$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
     
    
### [lease_info](#lease_info) - Lease information 
    
**Arguments:**    
- **ip**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$*    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
- **vlan_name**, pattern: *^.*$*    
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
- **dhcp_server**, pattern: *^.*$*    
     
    
### [link_info](#link_info) - Port information 
    
**Arguments:**    
- **port**, pattern: *^.*$*    
     
    
### [onu_reboot](#onu_reboot) - Reboot ONU 
    
**Arguments:**    
- **onu**, pattern: *.**, required    
     
    
### [pon_fdb](#pon_fdb) - Returned FDB table on ONTs 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
- **vlan_id**, pattern: *[0-9]{1,4}*    
- **mac**, pattern: *^[a-fA-F0-9:]{17}|[a-fA-F0-9]{12}$*    
     
    
### [pon_interface_info](#pon_interface_info) - Returned FDB table on ONTs 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
     
    
### [pon_interfaces_list](#pon_interfaces_list) - Information of PON interfaces 
     
    
### [pon_interfaces_tree](#pon_interfaces_tree) - Information of PON interfaces with onu and parent Ids 
    
**Arguments:**    
- **as_tree**, pattern: *yes|no*    
     
    
### [pon_ont_clear_counters](#pon_ont_clear_counters) - Clear counters on ONT (uni port) 
    
**Arguments:**    
- **interface**, pattern: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)\/?([0-9]{1,3})?$*, required    
     
    
### [pon_ont_delete](#pon_ont_delete) - Delete ont from system 
    
**Arguments:**    
- **interface**, pattern: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, required    
     
    
### [pon_ont_reboot](#pon_ont_reboot) - Reboot ONU by interface 
    
**Arguments:**    
- **interface**, pattern: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, required    
     
    
### [pon_ont_reset](#pon_ont_reset) - Reset ONT configuration 
    
**Arguments:**    
- **interface**, pattern: *^([0-9]{1,7})|((pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?)$*, required    
     
    
### [pon_onts_general_info](#pon_onts_general_info) - Returned ONTs MAC addresses 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
     
    
### [pon_onts_mac_addr](#pon_onts_mac_addr) - Returned ONTs MAC addresses 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
     
    
### [pon_onts_optical](#pon_onts_optical) - Returned ONTs MAC addresses 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
     
    
### [pon_onts_status](#pon_onts_status) - Returned onts statuses 
    
**Arguments:**    
- **meta**, pattern: *yes|no*    
     
    
### [pon_onts_status_detailed](#pon_onts_status_detailed) - Returned ONTs MAC addresses 
    
**Arguments:**    
- **interface**, pattern: *^(pon|xge|ge)([0-9])\/([0-9])\/([0-9]){1,}\:?([0-9]{1,3})?$*    
- **meta**, pattern: *yes|no*    
     
    
### [pon_registered_onts](#pon_registered_onts) - Count registered onts on pon 
     
    
### [pvid](#pvid) - PVID table 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [reboot](#reboot) - Reboot device 
     
    
### [rmon](#rmon) - RMON statistic 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [save_config](#save_config) - Save configuration 
     
    
### [sfp_info](#sfp_info) - SFP into 
    
**Arguments:**    
- **port**, pattern: *.**    
     
    
### [simple_queue_ctrl](#simple_queue_ctrl) - Info of simple queue 
    
**Arguments:**    
- **_id**, pattern: *.**    
- **action**, pattern: *^(remove|add|disable|enable)$*, required    
- **name**, pattern: *.**    
- **target**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, pattern: *.**    
- **limit-at**, pattern: *.**    
- **max-limit**, pattern: *.**    
- **parent**, pattern: *.**    
- **comment**, pattern: *.**    
     
    
### [simple_queue_info](#simple_queue_info) - Info of simple queue 
    
**Arguments:**    
- **_id**, pattern: *.**    
- **name**, pattern: *.**    
- **target**, pattern: *^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$|^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$*    
- **type**, pattern: *.**    
- **parent**, pattern: *.**    
     
    
### [slot_info](#slot_info) - Slot information (ZTE devices) 
    
**Arguments:**    
- **slot_num**, pattern: *^[0-9]{1,4}$*    
     
    
### [system](#system) - System information 
     
    
### [vlans](#vlans) - Vlan information 
    
**Arguments:**    
- **vlan_id**, pattern: *^[0-9]{1,4}$*    
     
    
### [vlans_by_port](#vlans_by_port) - Vlan information over port 
    
**Arguments:**    
- **port**, pattern: *^[0-9]{1,3}$*    
     
    
### [zte_card_list](#zte_card_list) - Listing of cards on OLT 
     
    
### [zte_fdb](#zte_fdb) - ONU signal strength info (on PON) 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*    
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*    
     
    
### [zte_gpon_onu_profile_list](#zte_gpon_onu_profile_list) - List ONU profiles for GPON 
    
**Arguments:**    
- **type**, pattern: *^(remote|line)$*, required    
     
    
### [zte_onu_dereg](#zte_onu_dereg) - Allow send configuration command to interface 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required    
     
    
### [zte_onu_ether_iface_info](#zte_onu_ether_iface_info) - ONU info (on PON) 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required    
     
    
### [zte_onu_info](#zte_onu_info) - ONU info (on PON) 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required    
     
    
### [zte_onu_interface_console](#zte_onu_interface_console) - Allow send configuration command to interface 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required    
- **command**, pattern: *.**, required    
     
    
### [zte_onu_pon_info](#zte_onu_pon_info) - Info about all ONUs on interface 
    
**Arguments:**    
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required    
     
    
### [zte_onu_registration_epon](#zte_onu_registration_epon) - ONU registration for GPON 
    
**Arguments:**    
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required    
- **type**, pattern: *.**, required    
- **mac**, pattern: *.**, required    
- **number**, pattern: *[0-9]{1,3}*, required    
     
    
### [zte_onu_registration_gpon](#zte_onu_registration_gpon) - ONU registration for GPON 
    
**Arguments:**    
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required    
- **type**, pattern: *.**, required    
- **serial**, pattern: *.**, required    
- **profile_line**, pattern: *.**, required    
- **profile_remote**, pattern: *.**, required    
- **number**, pattern: *[0-9]{1,3}*, required    
     
    
### [zte_onu_signal_strength](#zte_onu_signal_strength) - ONU signal strength info (on PON) 
    
**Arguments:**    
- **onu**, pattern: *^(gpon|epon)-(onu)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):[0-9]{1,3}$*, required    
     
    
### [zte_onu_state_by_interface](#zte_onu_state_by_interface) - List ONU state by interface 
    
**Arguments:**    
- **interface**, pattern: *^(gpon|epon)-(olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})$*, required    
- **parse**, pattern: *.**    
     
    
### [zte_unregistered_onu](#zte_unregistered_onu) - List unregistered ONU 
    
**Arguments:**    
- **type**, pattern: *^(all|gpon|epon)$*    
 