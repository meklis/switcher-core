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
    static
        /**
         * Описание encodeType
         * onu - если нужен индекс для ветки .1015, где число > 1000000000
         *
         * @param $encodeType
         * @param int $shelf
         * @param int $slot
         * @param int $olt
         * @param int $onu
         * @return float|int
         * @throws Exception
         */
    //@TODO Incorrect port encoding
    function ztePonIndexEncode($encodeType, $shelf = 0, $slot = 0 , $olt = 0, $onu = 0) {
        $addBit = function ($value, $count_bit) {
            while (strlen($value) < $count_bit) {
                $value = '0' . $value;
            }
            return $value;
        };
        if($shelf < 0 || $shelf > 256) {
            throw new InvalidArgumentException("Shelf number is incorrect");
        }
        if($slot < 0 || $slot > 256) {
            throw new InvalidArgumentException("Slot number is incorrect");
        }
        if($olt < 0) {
            throw new InvalidArgumentException("Olt number is incorrect");
        }
        $bits = "";
        switch ($encodeType) {
            case 'slot':
                $bits .= $addBit(decbin(1), 4);
                $bits .= $addBit(decbin($shelf), 4);
                $bits .= $addBit(decbin($slot), 8);
                $bits .= $addBit(decbin($olt) , 8);
                break;
            case 'onu':
                if($onu <= 0) {
                    throw new InvalidArgumentException("Incorrect onu number");
                }
                $bits .= $addBit(decbin(4), 4);
                $bits .= $addBit(decbin($shelf), 4);
                $bits .= $addBit(decbin($slot-1), 5);
                $bits .= $addBit(decbin($olt-1), 3);
                $bits .= $addBit(decbin($onu-1), 8);
                break;
            case 'eonu':
                if($onu <= 0) {
                    throw new InvalidArgumentException("Incorrect onu number");
                }
                $bits .= $addBit(decbin(3), 4);
                $bits .= $addBit(decbin($shelf), 4);
                $bits .= $addBit(decbin($slot), 5);
                $bits .= $addBit(decbin($olt-1), 3);
                $bits .= $addBit(decbin($onu), 8);
                break;
            default:
                throw new Exception("Unkown type $encodeType. Supported types: slot, onu, eonu");
        }
        $bits .= $addBit('', 8);
        return bindec($bits);
    }
    //@TODO Incorrect port decoding
    static function ztePonIndexDecode($id) {
        $bytes = str_split(decbin($id));
        while (count($bytes) < 32) {
            array_unshift($bytes, 0);
        }
        $convert = function ($arr, $offset, $length) {
            $arr = array_slice($arr, $offset, $length);
            $type = join($arr);
            return bindec($type);
        };
        $response = [
            'type' => $convert($bytes, 0, 4),
            'shelf' => $convert($bytes, 4, 4),
            'slot' => -1,
            'onu' => -1,
            'olt' => -1,
        ];
        switch ($response['type']) {
            case '1':
                $response['type'] = 'slot';
                $response['slot'] = $convert($bytes, 8, 8);
                $response['olt'] = $convert($bytes, 16, 8);
                break;
            case 3:
                $response['type'] = 'eonu';
                $response['slot'] = $convert($bytes, 8, 5);
                $response['olt'] = $convert($bytes, 13, 3) + 1;
                $response['onu'] = $convert($bytes, 16, 8);
                break;
            case 4:
                $response['type'] = 'onu';
                $response['slot'] = $convert($bytes, 8, 5) + 1;
                $response['olt'] = $convert($bytes, 13, 3) + 1;
                $response['onu'] = $convert($bytes, 16, 8) + 1;
                break;

            default:
                throw new Exception("Unknown type number = {$response['type']}");
        }
        return $response;
    }
}