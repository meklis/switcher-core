<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;


use DI\Annotation\Inject;
use Exception;
use InvalidArgumentException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;

abstract class C300ModuleAbstract extends AbstractModule
{

    /**
     * @Inject
     * @var TelnetLazyConnect
     */
    protected $telnet;

    public function parseInterface($name)
    {
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