<?php

class HuaweiPorts
{
    public static function decodeIfIndex($ifIndex)
    {
        $board_type = ($ifIndex & bindec('11111110000000000000000000000000')) >> 25;
        switch ($board_type) {
            case "126":  //EPON
                $port_type = "EPON";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111100000000')) >> 8;
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
            case "125":  //GPON
                $port_type = "GPON";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111100000000')) >> 8;
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
            case "123":  //xDSL
                $port_type = "xDSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "97":  //E1
                $port_type = "E1";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "96":  //BITS
                $port_type = "E1";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "63":  //
                $port_type = "DynamicMacIndex";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "61":  //
                $port_type = "DOCSIS_PORT";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "60":  //
                $port_type = "DOCSIS_DOWN_CHANNEL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "59":  //
                $port_type = "DOCSIS_UP_CHANNEL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "54":  //
                $port_type = "TRUNK";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "51":  //
                $port_type = "imaLink";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "48":  //VLAN
                $port_type = "VLAN";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $vlan_no = ($ifIndex & bindec('00000000000000000001111111111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "vlan" => $vlan_no));
            case "44":  //
                $port_type = "SHDSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "39":  //
                $port_type = "IMA";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
            case "7":  //Ethernet
                $port_type = "ethernet";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
            case "6":  //ADSL
                $port_type = "ADSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $interface_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "interface" => $interface_no));
            case "4":  //ADSL
                $port_type = "ATM";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $interface_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "interface" => $interface_no));
            default:
                echo "IFACE Board Type::[ " . $board_type . " ]\n";
                return (array("type" => "unknown", "board_code" => $board_type));
        }
    }

    public static function encodeIfIndexByName($ifName)
    {
        list($type, $name) = explode(" ", $ifName);
        switch ($type) {
            case 'EPON':
                break;
            case 'GPON':
                break;
            case 'ethernet':
                break;
            case 'xDSL':
                break;
            case 'DynamicMacIndex':
                break;
            case 'VLAN':
                break;
        }
        $board_type = ($ifIndex & bindec('11111110000000000000000000000000')) >> 25;
        switch ($board_type) {
            case "126":  //EPON
                $port_type = "EPON";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111100000000')) >> 8;
                //echo "GPON Shelf/Slot/Interface :: $shelf_no/$slot_no/$port_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
                break;
            case "125":  //GPON
                $port_type = "GPON";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111100000000')) >> 8;
                //echo "GPON Shelf/Slot/Interface :: $shelf_no/$slot_no/$port_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
                break;
            case "123":  //xDSL
                $port_type = "xDSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "97":  //E1
                $port_type = "E1";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "96":  //BITS
                $port_type = "E1";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "63":  //
                $port_type = "DynamicMacIndex";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "61":  //
                $port_type = "DOCSIS_PORT";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "60":  //
                $port_type = "DOCSIS_DOWN_CHANNEL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "59":  //
                $port_type = "DOCSIS_UP_CHANNEL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "54":  //
                $port_type = "TRUNK";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "51":  //
                $port_type = "imaLink";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "48":  //VLAN
                $port_type = "VLAN";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $vlan_no = ($ifIndex & bindec('00000000000000000001111111111111'));
                //echo "VLAN Shelf/Slot/vlan :: $shelf_no/$slot_no/$vlan_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "vlan" => $vlan_no));
                break;
            case "44":  //
                $port_type = "SHDSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "39":  //
                $port_type = "IMA";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $sn_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                $channel_no = ($ifIndex & bindec('00000000000000000000000000111111'));
                //echo "XDSL Shelf/Slot/SN/Channel :: $shelf_no/$slot_no/$sn_no/$channel_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "sn" => $sn_no, "channel" => $channel_no));
                break;
            case "7":  //Ethernet
                $port_type = "ethernet";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $port_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                //echo "ADSL Shelf/Slot/Interface :: $shelf_no/$slot_no/$interface_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "port" => $port_no));
                break;
            case "6":  //ADSL
                $port_type = "ADSL";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $interface_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                //echo "ADSL Shelf/Slot/Interface :: $shelf_no/$slot_no/$interface_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "interface" => $interface_no));
                break;
            case "4":  //ADSL
                $port_type = "ATM";
                $shelf_no = ($ifIndex & bindec('00000001111110000000000000000000')) >> 19;
                $slot_no = ($ifIndex & bindec('00000000000001111110000000000000')) >> 13;
                $interface_no = ($ifIndex & bindec('00000000000000000001111111000000')) >> 6;
                //echo "ADSL Shelf/Slot/Interface :: $shelf_no/$slot_no/$interface_no\n";
                return (array("type" => $port_type, "shelf" => $shelf_no, "slot" => $slot_no, "interface" => $interface_no));
                break;
            default:
                echo "IFACE Board Type::[ " . $board_type . " ]\n";
                return (array("type" => "unknown", "board_code" => $board_type));
                break;
        }
    }       // function
}