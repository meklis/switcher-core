<?php


namespace SwitcherCore\Modules;


use Exception;
use InvalidArgumentException;

class Helper
{
    public static function getBuildInConfig() {
           return realpath(__DIR__ . "/../../configs");
    }

    static function getIndexByOid($oid, $offset =0) {
        $exploded = explode(".", $oid);
        return $exploded[count($exploded) - 1 - $offset];
    }

    public static function oid2IP($oid) {
        return self::getIndexByOid($oid, 3) . '.' . self::getIndexByOid($oid, 2) . '.' . self::getIndexByOid($oid, 1) . '.' . self::getIndexByOid($oid); 
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
        if(!isset($filter['interface'])) $filter['interface'] = null;
        if(!isset($filter['vlan_id'])) $filter['vlan_id'] = 0;
        if(!isset($filter['disa_linkup_diag'])) $filter['disa_linkup_diag'] = true;
        if(!isset($filter['mac'])) $filter['mac'] = '';
        if(!isset($filter['type'])) $filter['type'] = '';
        if(!isset($filter['load_only'])) $filter['load_only'] = null;
    }
    static function oid2MacVlan($oid) {
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
    static function oid2VlanMac($oid) {
        $count = substr_count($oid, '.');
        $dec= explode('.', $oid);
        $vid = $dec[$count];
        if(strlen(dechex($dec[$count-6])) == 1) $m1 = '0'.dechex($dec[$count-6]); else $m1=dechex($dec[$count-6]);
        if(strlen(dechex($dec[$count-5])) == 1) $m2 = '0'.dechex($dec[$count-5]); else $m2=dechex($dec[$count-5]);
        if(strlen(dechex($dec[$count-4])) == 1) $m3 = '0'.dechex($dec[$count-4]); else $m3=dechex($dec[$count-4]);
        if(strlen(dechex($dec[$count-3])) == 1) $m4 = '0'.dechex($dec[$count-3]); else $m4=dechex($dec[$count-3]);
        if(strlen(dechex($dec[$count-2])) == 1) $m5 = '0'.dechex($dec[$count-2]); else $m5=dechex($dec[$count-2]);
        if(strlen(dechex($dec[$count-1])) == 1) $m6 = '0'.dechex($dec[$count-1]); else $m6=dechex($dec[$count-1]);

        return ['mac'=>strtoupper("$m1:$m2:$m3:$m4:$m5:$m6"), 'vid'=>$vid];
    }
    static function oid2mac($oid) {
        $count = substr_count($oid, '.');
        $dec= explode('.', $oid);
        if(strlen(dechex($dec[$count-5])) == 1) $m1 = '0'.dechex($dec[$count-5]); else $m1=dechex($dec[$count-5]);
        if(strlen(dechex($dec[$count-4])) == 1) $m2 = '0'.dechex($dec[$count-4]); else $m2=dechex($dec[$count-4]);
        if(strlen(dechex($dec[$count-3])) == 1) $m3 = '0'.dechex($dec[$count-3]); else $m3=dechex($dec[$count-3]);
        if(strlen(dechex($dec[$count-2])) == 1) $m4 = '0'.dechex($dec[$count-2]); else $m4=dechex($dec[$count-2]);
        if(strlen(dechex($dec[$count-1])) == 1) $m5 = '0'.dechex($dec[$count-1]); else $m5=dechex($dec[$count-1]);
        if(strlen(dechex($dec[$count])) == 1) $m6 = '0'.dechex($dec[$count]); else $m6=dechex($dec[$count]);

        return strtoupper("$m1:$m2:$m3:$m4:$m5:$m6");
    }

    static function oid2macArray($dec) {
        if(strlen(dechex($dec[0])) == 1) $m1 = '0'.dechex($dec[0]); else $m1=dechex($dec[0]);
        if(strlen(dechex($dec[1])) == 1) $m2 = '0'.dechex($dec[1]); else $m2=dechex($dec[1]);
        if(strlen(dechex($dec[2])) == 1) $m3 = '0'.dechex($dec[2]); else $m3=dechex($dec[2]);
        if(strlen(dechex($dec[3])) == 1) $m4 = '0'.dechex($dec[3]); else $m4=dechex($dec[3]);
        if(strlen(dechex($dec[4])) == 1) $m5 = '0'.dechex($dec[4]); else $m5=dechex($dec[4]);
        if(strlen(dechex($dec[5])) == 1) $m6 = '0'.dechex($dec[5]); else $m6=dechex($dec[5]);

        return strtoupper("$m1:$m2:$m3:$m4:$m5:$m6");
    }
    static function mac2oid($mac) {
        $mac = explode(":",strtoupper(str_replace(["-"," ","."],":",$mac)));
        $RESP = "";
        foreach ($mac as $otket) {
            $RESP .= ".".hexdec($otket);
        }
        return trim($RESP,".");
    }

    /**
     * Incomming mac must be aaaa.bbbb.cccc.dddd, a1-b2-c3-d4-e5-f6, or aabbccddeeff
     * Returned mac as AA:BB:CC:DD:EE:FF
     *
     * @param $macStr
     * @return string
     */
    static function formatMac($macStr) {
        $m = str_split(strtoupper(str_replace(['-', ':', '.', ','], '',$macStr)));
        if(count($m) < 12) {
            throw new InvalidArgumentException("Incorrect mac address - $macStr");
        }
        return "{$m[0]}{$m[1]}:{$m[2]}{$m[3]}:{$m[4]}{$m[5]}:{$m[6]}{$m[7]}:{$m[8]}{$m[9]}:{$m[10]}{$m[11]}";
    }
    static function formatMac3Blocks($macStr, $delim = '.') {
        $m = str_split(strtolower(str_replace(['-', ':', '.', ','], '',$macStr)));
        if(count($m) < 12) {
            throw new InvalidArgumentException("Incorrect mac address - $macStr");
        }
        return "{$m[0]}{$m[1]}{$m[2]}{$m[3]}{$delim}{$m[4]}{$m[5]}{$m[6]}{$m[7]}{$delim}{$m[8]}{$m[9]}{$m[10]}{$m[11]}";
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
    static function hexToStr($string) {
            $symbols = explode(":", strtoupper(str_replace(["-", " ", "."], ":", $string)));
            $str = '';
            $char = '';
            foreach ($symbols as $symbol) {
                if(!hexdec($symbol)) continue;
                $char = chr(hexdec($symbol));
                if(!mb_detect_encoding($char, 'Windows-1251', true) && !mb_detect_encoding($char, 'ASCII', true)) {
                    continue;
                }
                $str .= $char;
            }
            if(mb_detect_encoding($char, 'ASCII', true)) {
                return iconv("ASCII", "UTF-8//IGNORE", $str,);;
            }
            if(mb_detect_encoding($char, 'Windows-1251', true)) {
                return iconv("Windows-1251", "UTF-8//IGNORE", $str,);
            }
            return  '';
    }

    /**
     * @param $data
     * @return array
     */
    static function getTrapElementByName($trapParsedData, $searchingName)
    {
        $data = array_filter($trapParsedData, function ($e) use ($searchingName) {
           return $e['name'] == $searchingName;
        });
        if(count($data) > 0) {
            return array_values($data)[0];
        }
        return null;
    }
}