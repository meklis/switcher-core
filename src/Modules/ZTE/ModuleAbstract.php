<?php


namespace SwitcherCore\Modules\ZTE;


use Exception;
use InvalidArgumentException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class ModuleAbstract extends AbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $telnet;


    function encodeSnmpOid($value) {
        $type = '';
        $shelf = 0;
        $slot = 0;
        $port = 0;
        $onuNum = 0;
        $fillData = function ($val, $size) {
            $decoded = decbin($val);
            while (strlen($decoded) < $size) {
                $decoded = "0" . $decoded;
            }
            return $decoded;
        };

        if (preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})(:([0-9]{1,}))?$/', $value, $matches)) {
            $type = $matches[1];
            $shelf = (int)$matches[3];
            $slot = (int)$matches[4];
            $port = (int)$matches[5];
            $onuNum = isset($matches[7]) ? (int)$matches[7] : 0;
        }
        switch ($type) {
            case 'gpon':
                $binary = "0001" .
                    $fillData($shelf - $this->model->getExtraParamByName('port_offset'), 4) .
                    $fillData($slot, 8) .
                    $fillData($port, 8) .
                    $fillData(0, 8)
                ;
                $data = bindec($binary);
                if($onuNum) {
                    return  "{$data}.$onuNum";
                }
                return  $data;
            case 'epon':
                $binary = "0011" .
                    $fillData($shelf - $this->model->getExtraParamByName('port_offset'), 4) .
                    $fillData($slot, 5) .
                    $fillData($port - 1, 3) .
                    $fillData($onuNum,8) .
                    $fillData(0, 8);
                ;
                return bindec($binary);
            default:
                throw new \Exception("Error parse interface $value");
        }

    }


    function decodeSnmpOid($oid)
    {
        $onuNum = 0;
        if(preg_match('/^([0-9]{1,}?)\.([0-9]{1,})$/', $oid, $matches)) {
            $onuNum = $matches[2];
            $oid = $matches[1];
        }
        $binary = decbin($oid);
        while (strlen($binary) < 32) {
            $binary = "0" . $binary;
        }
        $type = bindec(substr($binary, 0, 4));
        $data = substr($binary, 4);
        $shelf = 0;
        $slot = 0;
        $portOlt = 0;
        $vPort = 0;
        $decodeType = '';
        switch ($type) {
            case 1:
                $shelf = bindec(substr($data, 0, 4)) + $this->model->getExtraParamByName('port_offset');
                $slot = bindec(substr($data, 4, 8));
                $portOlt = bindec(substr($data, 12, 8));
                $decodeType = 'gpon';
                break;
            case 3:
            case 4:
                $decodeType = 'epon';
                $shelf = bindec(substr($data, 0, 4))  + $this->model->getExtraParamByName('port_offset');
                $slot = bindec(substr($data, 4, 5));
                $portOlt = bindec(substr($data, 9, 3)) + 1;
                $onuNum = bindec(substr($data, 12, 8));
                $vPort = bindec(substr($data, 20, 8));
                break;
        }
        return [
            'type' => $decodeType,
            'shelf' => $shelf,
            'slot' => $slot,
            'port' => $portOlt,
            'onu_number' => $onuNum,
            'v_port' => $vPort,
        ];
    }


    public function parseInterface($name)
    {
        //Это ID из snmp
        $oidID = 0;
        if((is_numeric($name) && $name > 9999999) || preg_match('/^([0-9]{1,})\.([0-9]{1,})$/', $name) ) {
            $oidID = $name;
            $result = $this->decodeSnmpOid($name);
            if($result['onu_number']) {
                $name = "{$result['type']}-onu_{$result['shelf']}/{$result['slot']}/{$result['port']}:{$result['onu_number']}";
            } else {
                $name = "{$result['type']}-olt_{$result['shelf']}/{$result['slot']}/{$result['port']}";
            }
        }

        if (preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})/', $name, $matches)) {
            $onu = null;
            $type = 'PON';
            $id =
                ($matches[3] * 1000000) +
                ($matches[4] * 100000) +
                ($matches[5] * 1000);
            $parent = null;
            if ($matches[2] == 'onu' && preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})/', $name, $m)) {
                $onu = $m[6];
                $type = 'ONU';
                $parent = $id;
                $id += $onu;
            }
            return [
                'name' => $name,
                'id' => (int)$id,
                'type' => $type,
                'parent' => (int)$parent,
                'technology' => $matches[1],
                'is_onu' => $matches[2] === 'onu',
                'is_port' => $matches[2] === 'olt',
                'shelf' => (int)$matches[3],
                'slot' => (int)$matches[4],
                'port' => (int)$matches[5],
                'onu_num' => (int)$onu,
                '_oid_id' => $oidID ? $oidID : $this->encodeSnmpOid($name),
            ];
        }

        if(is_numeric($name)) {
            $shelf = floor($name / 1000000);
            $slot = floor(($name - ($shelf * 1000000)) / 100000);
            $port = floor(($name - (($slot * 100000) + ($shelf * 1000000))) / 1000);
            $onu = floor(($name - (($port * 1000) + ($slot * 100000) + ($shelf * 1000000))));
            $technology = '';
            $cards = $this->getModule('zte_card_list')->run()->getPretty();
            $cardTypes = [];
            foreach ($this->model->getExtra()['card_types'] as $type) {
                $cardTypes[$type['name']] = $type;
            }
            foreach ($cards as $card) {
                if($card['shelf'] == $shelf && $card['slot'] == $slot && isset($cardTypes[$card['real_type']])) {
                    $technology = $cardTypes[$card['real_type']]['interface_type'];
                }
            }
            if($onu) {
                $parent = (int)$name - $onu;
                $type = 'ONU';
                $interface = "{$technology}-onu_{$shelf}/{$slot}/{$port}:{$onu}";
            } else {
                $parent = null;
                $type = 'PON';
                $interface = "{$technology}-olt_{$shelf}/{$slot}/{$port}";
            }
            return [
                'id' => (int)$name,
                'type' => $type,
                'name' => $interface,
                'parent' => $parent,
                'technology' => $technology,
                'is_onu' => $onu ? true : false,
                'is_port' => $onu ? false : true,
                'shelf' => $shelf,
                'slot' => $slot,
                'port' => $port,
                'onu_num' => (int)$onu,
                '_oid_id' => $this->encodeSnmpOid($interface),
            ];
        }
        throw new InvalidArgumentException("Error parse port with name '$name'");
    }
    protected function exec($command) {
        $response = $this->telnet->exec($command);
        if(!trim($response)) return true;
        if(preg_match('/^\%Info/', $response)) return  true;
        if(preg_match('/\[Successful\]/', $response)) return  true;
        if(preg_match('/\[OK\]/', $response)) return  true;
        if(preg_match('/Invalid input detected/', $response)) throw new Exception("Invalid input detected for command '$command'");
        throw new Exception("Unknown response for command '$command' - \n>>>{$response}<<<");
    }
    public function macTo6octets($mac) {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if(count($m) < 12) {
            throw new Exception("Received incorrect MAC-address");
        }
        return strtoupper("{$m[0]}{$m[1]}:{$m[2]}{$m[3]}:{$m[4]}{$m[5]}:{$m[6]}{$m[7]}:{$m[8]}{$m[9]}:{$m[10]}{$m[11]}");
    }
    public function macTo3octets($mac) {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if(count($m) < 12) {
            throw new Exception("Received incorrect MAC-address");
        }
        return strtolower("{$m[0]}{$m[1]}{$m[2]}.{$m[3]}{$m[4]}{$m[5]}.{$m[6]}{$m[7]}{$m[8]}.{$m[9]}{$m[10]}{$m[11]}");
    }
    private function fromCamelCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    function parseTable($rows) {
        if(!is_array($rows)) {
            $rows = explode("\n", $rows);
        }
        $width = 0;
        foreach ($rows as $num=>$row) {
            if(strlen($row) > $width) {
                $width = strlen($row);
            } elseif (strlen($row) === 0) {
                unset($rows[$num]);
            }
        }
        $rows = array_values($rows);
        $symbolBlock = array_fill(0, $width, true);
        foreach ($rows as $row) {
            if(preg_match('/^[-]{1,}$/', $row)) continue;
            if(str_contains($row, "---")) continue;
            foreach (str_split($row) as $num=>$symbol) {
                if($symbol === ' ' || $symbol === '\t') continue;
                $symbolBlock[$num] = false;
            }
        }
        $cols = [];
        $start = 0;
        $mustBeEnd = false;
        foreach ($symbolBlock as $num=>$is_space) {
            if($is_space && !$mustBeEnd) {
                $mustBeEnd = true;
            } elseif (!$is_space && $mustBeEnd) {
                $mustBeEnd = false;
                $cols[] = [
                    'start' => $start,
                    'stop' => $num-1,
                    'name' =>  $this->fromCamelCase(str_replace([' ', '-', ':'], '_', trim(substr($rows[0], $start, $num-1 - $start)))),
                    'size' => $num-1 - $start,
                ];
                $start = $num;
            }
        }
        $response = [];
        foreach (array_splice($rows,2) as $row) {
            $resp = [];
            if(!trim($row)) continue;
            foreach ($cols as $cell) {
                $resp[$cell['name']] = trim(substr($row, $cell['start'], $cell['size']));
            }
            $response[] = $resp;
        }
        return $response;
    }

    function convertHexToString($string) {
        $symbols = explode(":", $string);
        $str = '';
        $cp1251Detected = false;
        foreach ($symbols as $symbol) {
            if(!hexdec($symbol)) continue;
            $char = chr(hexdec($symbol));
            if(mb_detect_encoding($char, 'ASCII', true)) {

            } elseif (mb_detect_encoding($char, 'Windows-1251', true)) {
                $cp1251Detected = true;
            } else {
                continue;
            }
            $str .= $char;
        }
        if($cp1251Detected) {
            return iconv("WINDOWS-1251", "UTF-8//IGNORE", $str,);
        }
        return  $str;
    }

    function parseExpandedTable($input) {
        $responses = [];
        $r  = [];
        if(preg_match('/^\%Error/', trim($input))) {
            throw new Exception("Device returned error - '$input'");
        }
        $lines = explode("\n", trim($input));
        if(count($lines) < 2) {
            throw new Exception("Unknown input - '" . join($lines) . "'");
        }
        foreach ($lines as $line) {
            if(preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $key = $this->fromCamelCase(str_replace([' ', '-'], '_', trim($m[1])));
                $r[$key] = trim($m[2]);
            } elseif (!trim($line)) {
                if(count($r) > 0) {
                    $responses[] = $r;
                }
                $r = [];
            }
        }
        if(count($r) > 0) {
            $responses[] = $r;
        }
        return $responses;
    }
}