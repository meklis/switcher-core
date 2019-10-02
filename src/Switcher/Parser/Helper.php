<?php


namespace SnmpSwitcher\Switcher\Parser;


class Helper
{

    static function getIndexByOid($oid) {
        $exploded = explode(".", $oid);
        return $exploded[count($exploded) - 1];
    }

    static function fromCamelCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
    static function prepareFilter(&$filter) {
        if(!isset($filter['port'])) $filter['port'] = 0;
        if(!isset($filter['vlan_id'])) $filter['vlan_id'] = 0;
        if(!isset($filter['disa_linkup_diag'])) $filter['disa_linkup_diag'] = true;
        if(!isset($filter['mac'])) $filter['mac'] = '';
        if(!isset($filter['type'])) $filter['type'] = '';
    }
    static function oid2mac($oid) {
        $count = substr_count($oid, '.');
        $dec= explode('.', $oid);
        $vid = $dec[$count-6];
        if(strlen(dechex($dec[$count-5])) == 1) $m1 = '0'.dechex($dec[$count-5]); else $m1=dechex($dec[$count-5]);
        if(strlen(dechex($dec[$count-4])) == 1) $m2 = '0'.dechex($dec[$count-4]); else $m2=dechex($dec[$count-4]);
        if(strlen(dechex($dec[$count-3])) == 1) $m3 = '0'.dechex($dec[$count-3]); else $m3=dechex($dec[$count-3]);
        if(strlen(dechex($dec[$count-2])) == 1) $m4 = '0'.dechex($dec[$count-2]); else $m4=dechex($dec[$count-2]);
        if(strlen(dechex($dec[$count-1])) == 1) $m5 = '0'.dechex($dec[$count-1]); else $m5=dechex($dec[$count-1]);
        if(strlen(dechex($dec[$count])) == 1) $m6 = '0'.dechex($dec[$count]); else $m6=dechex($dec[$count]);

        return ['mac'=>strtoupper("$m1:$m2:$m3:$m4:$m5:$m6"), 'vid'=>$vid];
    }
    static function mac2oid($mac) {
        $mac = explode(":",strtoupper(str_replace(["-"," ","."],":",$mac)));
        $RESP = "";
        foreach ($mac as $otket) {
            $RESP .= ".".hexdec($otket);
        }
        return trim($RESP,".");
    }
    static function hexToBinStr($hex) {
        $oktets = explode(":", str_replace([' ', '-', '.'], ":", $hex));
        $dex = '';
        foreach($oktets as $val) {
            $decs = decbin(hexdec($val));
            while(strlen($decs)<8) $decs = "0".$decs;
            $dex .=$decs;
        }
        return $dex;
    }
}