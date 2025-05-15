<?php


namespace SwitcherCore\Modules\ZTE;


use Exception;
use InvalidArgumentException;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

abstract class ModuleAbstract extends AbstractModule
{
    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $telnet;


    function getCardInfoBy($shelf, $slot)
    {
        $cards = array_filter($this->getCardListWithStatuses(), function ($e) use ($shelf, $slot) {
            return $shelf == $e['shelf'] && $slot == $e['slot'];
        });
        if (count($cards) > 0) {
            return array_values($cards)[0];
        }
        throw new \Exception("Card not found by shelf={$shelf}, slot={$slot}");
    }

    function encodeSnmpOid($value, $type = null)
    {
        $shelf = 0;
        $slot = 0;
        $port = 0;
        $onuNum = 0;
        $fillData = function ($val, $size) {
            return str_pad(decbin($val), $size, '0', STR_PAD_LEFT);
        };
        $detectedType = '';
        if (preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})(:([0-9]{1,}))?$/', $value, $matches)) {
            if (!$type) {
                $detectedType = $matches[1];
            }
            $shelf = (int)$matches[3];
            $slot = (int)$matches[4];
            $port = (int)$matches[5];
            $onuNum = isset($matches[7]) ? (int)$matches[7] : 0;
        }

        $card = $this->getCardInfoBy($shelf, $slot);
        if ($card['technology'] === 'epon' && $card['num_ports'] > 8 && !$type) {
            $type = '16_port';
        }
        if (!$type) {
            $type = $detectedType;
        }

        switch ($type) {
            case 'gpon':
                $binary = "0001" .
                    $fillData($shelf - $this->model->getExtraParamByName('port_offset'), 4) .
                    $fillData($slot, 8) .
                    $fillData($port, 8) .
                    $fillData(0, 8);
                $data = bindec($binary);
                if ($onuNum) {
                    return "{$data}.$onuNum";
                }
                return $data;
            case 'gpon_eth':
                $binary = "0001" .
                    $fillData(1, 4) .
                    $fillData($shelf, 8) .
                    $fillData($slot, 8) .
                    $fillData($port, 8);
                $data = (string)bindec($binary);
                if ($onuNum) {
                    return "{$data}.$onuNum";
                }
                return $data;
            case 'xpon':
            case 'epon':
                $binary = "0011" .
                    $fillData($shelf - $this->model->getExtraParamByName('port_offset'), 4) .
                    $fillData($slot, 5) .
                    $fillData($port - 1, 3) .
                    $fillData($onuNum, 8) .
                    $fillData(0, 8);;
                return bindec($binary);
            case '16_port':
                $binary = "1001" .
                    $fillData($shelf - $this->model->getExtraParamByName('port_offset'), 3) .
                    $fillData($slot, 5) .
                    $fillData($port - 1, 4) .
                    $fillData($onuNum, 8) .
                    $fillData(0, 8);;
                return bindec($binary);
            default:
                throw new \Exception("Error parse interface $value");
        }
    }

    function decodeSnmpOid($oid)
    {
        $onuNum = 0;
        if (preg_match('/^([0-9]{1,}?)\.([0-9]{1,})$/', $oid, $matches)) {
            $onuNum = $matches[2];
            $oid = $matches[1];
        }
        $binary = str_pad(decbin($oid), 32, '0', STR_PAD_LEFT);
        $type = bindec(substr($binary, 0, 4));
        $shelf = 0;
        $slot = 0;
        $portOlt = 0;
        $vPort = 0;
        switch ($type) {
            case 1:
                $shelf = bindec(substr($binary, 4, 4)) + 1;
                $slot = bindec(substr($binary, 8, 8));
                $portOlt = bindec(substr($binary, 16, 8));
                break;
            case 3:
                $shelf = bindec(substr($binary, 4, 4)) + 1;
                $slot = bindec(substr($binary, 8, 5));
                $portOlt = bindec(substr($binary, 13, 3)) + 1;
                $onuNum = bindec(substr($binary, 16, 8));
                $vPort = bindec(substr($binary, 24, 8));
                break;
            case 9:
                //Временно отключил для проведения тестов
                //$shelf = bindec(substr($binary, 4, 3)) + 1;
                //$slot = bindec(substr($binary, 7, 4)) + 1;
                $shelf = bindec(substr($binary, 4, 2)) + 1;
                $slot = bindec(substr($binary, 8, 4)) ;
                $portOlt = bindec(substr($binary, 12, 4)) + 1;
                $onuNum = bindec(substr($binary, 16, 8));
                $vPort = bindec(substr($binary, 24, 8));
                break;
        }
        $card = $this->getCardInfoBy($shelf, $slot);
        if($card['oper_status'] !== 'inService') {
            throw new \Exception("Error decode port on not in service card. shelf={$shelf}, slot={$slot}");
        }
        $be = [
            '_decode_type' => $type,
            'type' => $card['technology'],
            'shelf' => $shelf,
            'slot' => $slot,
            'port' => $portOlt,
            'onu_number' => $onuNum,
            'v_port' => $vPort,
        ];
        return $be;
    }

    private $_xidInterfaces;

    function listInterfacesByXidNames()
    {
        if ($this->_xidInterfaces) {
            return $this->_xidInterfaces;
        }
        if ($ifaces = $this->getCache('xid_interfaces', true)) {
            $this->_xidInterfaces = $ifaces;
            return $ifaces;
        }
        $resp = $this->formatResponse($this->snmp->walk([Oid::init($this->oids->getOidByName('if.Name')->getOid())]));
        if ($resp['if.Name']->error()) {
            throw new \Exception($resp['if.Name']->error());
        }
        $response = [];
        foreach ($resp['if.Name']->fetchAll() as $f) {
            if (preg_match('/^(gpon|epon|gei|xgei)[-_]([0-9]{1,3})\/([0-9]{1,3})\/([0-9]{1,3})$/', trim($f->getValue()), $m)) {
                $type = null;
                if (in_array($m[1], ['epon', 'gpon'])) {
                    $type = 'PON';
                }
                if ($m[1] === 'gei') {
                    $type = 'GE';
                }
                if ($m[1] === 'xgei') {
                    $type = 'TGE';
                }
                $id =
                    ($m[2] * 10000000) +
                    ($m[3] * 100000) +
                    ($m[4] * 1000);
                $response["{$m[2]}/{$m[3]}/{$m[4]}"] = [
                    'id' =>  (int)$id,
                    '_xid' => Helper::getIndexByOid($f->getOid()),
                    'type' => $type,
                    '_shelf' => $m[2],
                    '_slot' => $m[3],
                    '_port' => $m[4],
                    'name' => $f->getValue(),
                    'parent' => null,
                    '_technology' => in_array($m[1], ['epon', 'gpon']) ? $m[1] : null,
                    '_oid_id' => in_array($m[1], ['gpon', 'epon']) ? $this->encodeSnmpOid("{$m[1]}-olt_{$m[2]}/{$m[3]}/{$m[4]}") : null,
                    '_oid_eth_id' => in_array($m[1], ['gpon', 'epon']) ? $this->encodeSnmpOid("{$m[1]}-olt_{$m[2]}/{$m[3]}/{$m[4]}", "gpon_eth") : null,
                    '_xpon_id' => null,
                ];
            } elseif (preg_match('/^(gpon|epon|gei|xgei)[-_]olt[-_]([0-9]{1,3})\/([0-9]{1,3})\/([0-9]{1,3})$/', trim($f->getValue()), $m)) {
                $type = null;
                if (in_array($m[1], ['epon', 'gpon'])) {
                    $type = 'PON';
                }
                if ($m[1] === 'gei') {
                    $type = 'GE';
                }
                if ($m[1] === 'xgei') {
                    $type = 'TGE';
                }
                $id =
                    ($m[2] * 10000000) +
                    ($m[3] * 100000) +
                    ($m[4] * 1000);
                $response["{$m[2]}/{$m[3]}/{$m[4]}"] = [
                    'id' =>  (int)$id,
                    '_xid' => Helper::getIndexByOid($f->getOid()),
                    'type' => $type,
                    '_shelf' => $m[2],
                    '_slot' => $m[3],
                    '_port' => $m[4],
                    'name' => $f->getValue(),
                    'parent' => null,
                    '_technology' => in_array($m[1], ['epon', 'gpon']) ? $m[1] : null,
                    '_oid_id' => in_array($m[1], ['gpon', 'epon']) ? $this->encodeSnmpOid("{$m[1]}-olt_{$m[2]}/{$m[3]}/{$m[4]}") : null,
                    '_oid_eth_id' => in_array($m[1], ['gpon', 'epon']) ? $this->encodeSnmpOid("{$m[1]}-olt_{$m[2]}/{$m[3]}/{$m[4]}", "gpon_eth") : null,
                    '_xpon_id' => null,
                ];
            }
        }
        $this->_xidInterfaces = $response;
        $this->setCache('xid_interfaces', $response, 60, true);
        return $response;
    }


    public function isGponCardsExist()
    {
        $cards = $this->getCardListWithStatuses();
        $gponCards = array_filter($cards, function ($c) {
            return $c['technology'] == 'gpon' && $c['oper_status'] == 'inService';
        });
        return count($gponCards) > 0;
    }

    private $_cardStatuses = [];
    protected function getCardListWithStatuses() {
        if($this->_cardStatuses) {
            return  $this->_cardStatuses;
        }
        $resp = [];
        foreach ($this->getModule('card_list')->run()->getPretty() as $card) {
            $resp["{$card['shelf']}-{$card['slot']}"] = $card;
        }
        foreach ($this->getModule('card_status')->run()->getPretty() as $card) {
            if(!isset($resp["{$card['shelf']}-{$card['slot']}"])) continue;
            $resp["{$card['shelf']}-{$card['slot']}"]['oper_status'] = $card['oper_status'];
            $resp["{$card['shelf']}-{$card['slot']}"]['admin_status'] = $card['admin_status'];
            $resp["{$card['shelf']}-{$card['slot']}"]['cpu_load'] = $card['cpu_load'];
            $resp["{$card['shelf']}-{$card['slot']}"]['temperature'] = $card['temperature'];
        }
        $this->_cardStatuses = $resp;
        return $resp;
    }

    public function isEponCardsExist()
    {
        $cards = $this->getCardListWithStatuses();
        $gponCards = array_filter($cards, function ($c) {
            return $c['technology'] == 'epon' && $c['oper_status'] == 'inService';
        });
        return count($gponCards) > 0;
    }


    public function parseInterface($name, $parseBy = 'id')
    {
        $oidID = 0;
        $xidList = $this->listInterfacesByXidNames();

        if ($parseBy == 'eth_id') {
            [$oidBlock, $onuId] = explode(".", $name);
            $find = array_filter($xidList, function ($e) use ($oidBlock) {
                return $e['_oid_eth_id'] == $oidBlock;
            });
            if (count($find) > 0) {
                $iface = array_values($find)[0];
                $name = "{$iface['_technology']}-onu_{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}:{$onuId}";
            } else {
                throw new \Exception("Error find interface by eth_id with id={$name}");
            }
        }
        //Попытка распарсить интерфейсы по ID ОЛТа ZTE. При этом переназначается переменная $name, которая будет содержать имя интерфейса
        if (($parseBy == 'id' && is_numeric($name) && $name > 19999999) || preg_match('/^([0-9]{1,})\.([0-9]{1,})$/', $name)) {
            $result = $this->decodeSnmpOid($name);
            if ($result['onu_number']) {
                $name = "{$result['type']}-onu_{$result['shelf']}/{$result['slot']}/{$result['port']}:{$result['onu_number']}";
            } else {
                $name = "{$result['type']}-olt_{$result['shelf']}/{$result['slot']}/{$result['port']}";
            }
        } elseif ($parseBy == 'xid' && is_numeric($name)) {
            $find = array_filter($xidList, function ($e) use ($name) {
                return $e['_xid'] == $name;
            });
            if (count($find) > 0) {
                return array_values($find)[0];
            }
        }
        //Попытка распарсить интерфейс PON по его имени
        if (preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})/', $name, $matches)) {
            $onu = null;
            $type = 'PON';
            $id =
                ($matches[3] * 10000000) +
                ($matches[4] * 100000) +
                ($matches[5] * 1000);
            $parent = (int)"101{$matches['3']}{$matches['4']}";
            if ($matches[2] == 'onu' && preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})/', $name, $m)) {
                $onu = $m[6];
                $type = 'ONU';
                $parent = $id;
                $id += $onu;
            }
            $xponId = null;
            $maxOntSize = 0;
            if(in_array($matches[1], ['gpon', 'epon'])) {
                $card = $this->getCardInfoBy((int)$matches[3], (int)$matches[4]);
                if($card['oper_status'] !== 'inService') {
                    throw new \Exception("Error parse port on not in service card. shelf={$matches[3]}, slot={$matches[4]}");
                }
                if($card['num_ports'] > 8) {
                    $xponId = $this->encodeSnmpOid($name, '16_port');
                }  else {
                    $xponId = $this->encodeSnmpOid($name, 'xpon');
                }
                if(preg_match('/^ETTO/',$card['cfg_type'])) {
                    $maxOntSize = 128;
                } elseif($card['technology'] === 'epon') {
                    $maxOntSize = 64;
                } elseif($card['technology'] === 'gpon') {
                    $maxOntSize = 128;
                }
            }
            return [
                'name' => $name,
                'id' => (int)$id,
                'type' => $type,
                'parent' => (int)$parent,
                '_pon_max_ont_size' => $maxOntSize,
                '_technology' => $matches[1],
                '_shelf' => (int)$matches[3],
                '_slot' => (int)$matches[4],
                '_port' => (int)$matches[5],
                '_onu' => (int)$onu,
                '_oid_id' => $oidID ? $oidID : $this->encodeSnmpOid($name),
                '_gpon_format' => $this->encodeSnmpOid($name, 'gpon'),
                '_xid' => 0,
                '_oid_eth_id' => in_array($matches[1], ['gpon', 'epon']) ? $this->encodeSnmpOid($name, "gpon_eth") : null,
                '_xpon_id' => $xponId,
            ];
        }

        if (is_numeric($name)) {
            $shelf = floor($name / 10000000);
            $slot = floor(($name - ($shelf * 10000000)) / 100000);

            $port = floor(($name - (($slot * 100000) + ($shelf * 10000000))) / 1000);
            $onu = floor(($name - (($port * 1000) + ($slot * 100000) + ($shelf * 10000000))));
            $card = $this->getCardInfoBy($shelf, $slot);
            if($card['oper_status'] !== 'inService') {
                throw new \Exception("Error parse port on not in service card. shelf={$matches[3]}, slot={$matches[4]}");
            }
            $technology = $card['technology'];
            if ((!$technology || !$onu) && isset($xidList["{$shelf}/{$slot}/{$port}"])) {
                return  $xidList["{$shelf}/{$slot}/{$port}"];
            } elseif (!$technology) {
                throw new \Exception("Error get technology");
            }
            if ($onu) {
                $parent = (int)$name - $onu;
                $type = 'ONU';
                $interface = "{$technology}-onu_{$shelf}/{$slot}/{$port}:{$onu}";
            } else {
                $parent = null;
                $type = 'PON';
                $interface = "{$technology}-olt_{$shelf}/{$slot}/{$port}";
            }

            $xponId = null;
            $maxOntSize = 0;
            if(in_array($technology, ['gpon', 'epon'])) {
                $card = $this->getCardInfoBy($shelf,$slot);
                if($card['oper_status'] !== 'inService') {
                    throw new \Exception("Error parse port on not in service card. shelf={$matches[3]}, slot={$matches[4]}");
                }
                if($card['num_ports'] > 8) {
                    $xponId = $this->encodeSnmpOid("{$technology}-olt_{$shelf}/{$slot}/{$port}:$onu", '16_port');
                }  else {
                    $xponId = $this->encodeSnmpOid("{$technology}-olt_{$shelf}/{$slot}/{$port}:$onu", 'xpon');
                }

                if(preg_match('/^ETTO/',$card['cfg_type'])) {
                    $maxOntSize = 128;
                } elseif($card['technology'] === 'epon') {
                    $maxOntSize = 64;
                } elseif($card['technology'] === 'gpon') {
                    $maxOntSize = 128;
                }
            }
            return [
                'id' => (int)$name,
                'type' => $type,
                'name' => $interface,
                'parent' => $parent,
                '_technology' => $technology,
                '_pon_max_ont_size' => $maxOntSize,
                '_shelf' => $shelf,
                '_slot' => $slot,
                '_port' => $port,
                '_onu' => (int)$onu,
                '_oid_id' => $this->encodeSnmpOid($interface),
                '_gpon_format' => $this->encodeSnmpOid($interface, 'gpon'),
                '_xid' => 0,
                '_xid_name' => '',
                '_oid_eth_id' => in_array($technology, ['gpon', 'epon']) ? $this->encodeSnmpOid("{$technology}-olt_{$shelf}/{$slot}/{$port}:$onu", "gpon_eth") : null,
                '_xpon_id' => $xponId,
            ];
        }


        if (is_string($name)) {
            $find = array_filter($xidList, function ($e) use ($name) {
                return $e['name'] == $name;
            });
            if (count($find) > 0) {
                return array_values($find)[0];
            }
        }

        throw new InvalidArgumentException("Error parse port with name '$name'");
    }

    protected function exec($command)
    {
        $response = $this->telnet->exec($command);
        if (!trim($response)) return true;
        if (preg_match('/^\%Info/', $response)) return true;
        if (preg_match('/^Enter configuration commands/', $response)) return true;
        if (preg_match('/\[Successful\]/', $response)) return true;
        if (preg_match('/\[OK\]/', $response)) return true;
        if (preg_match('/Invalid input detected/', $response)) throw new Exception("Invalid input detected for command '$command'");
        throw new Exception("Unknown response for command '$command' - \n>>>{$response}<<<");
    }

    public function macTo6octets($mac)
    {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if (count($m) < 12) {
            throw new Exception("Received incorrect MAC-address");
        }
        return strtoupper("{$m[0]}{$m[1]}:{$m[2]}{$m[3]}:{$m[4]}{$m[5]}:{$m[6]}{$m[7]}:{$m[8]}{$m[9]}:{$m[10]}{$m[11]}");
    }

    public function macTo3octets($mac)
    {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if (count($m) < 12) {
            throw new Exception("Received incorrect MAC-address");
        }
        return strtolower("{$m[0]}{$m[1]}{$m[2]}.{$m[3]}{$m[4]}{$m[5]}.{$m[6]}{$m[7]}{$m[8]}.{$m[9]}{$m[10]}{$m[11]}");
    }

    private function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    function parseTable($rows)
    {
        if (!is_array($rows)) {
            $rows = explode("\n", $rows);
        }
        $width = 0;
        foreach ($rows as $num => $row) {
            if (strlen($row) > $width) {
                $width = strlen($row);
            } elseif (strlen($row) === 0) {
                unset($rows[$num]);
            }
        }
        $rows = array_values($rows);
        $symbolBlock = array_fill(0, $width, true);
        foreach ($rows as $row) {
            if (preg_match('/^[-]{1,}$/', $row)) continue;
            if (str_contains($row, "---")) continue;
            foreach (str_split($row) as $num => $symbol) {
                if ($symbol === ' ' || $symbol === '\t') continue;
                $symbolBlock[$num] = false;
            }
        }
        $cols = [];
        $start = 0;
        $mustBeEnd = false;
        foreach ($symbolBlock as $num => $is_space) {
            if ($is_space && !$mustBeEnd) {
                $mustBeEnd = true;
            } elseif (!$is_space && $mustBeEnd) {
                $mustBeEnd = false;
                $cols[] = [
                    'start' => $start,
                    'stop' => $num - 1,
                    'name' => $this->fromCamelCase(str_replace([' ', '-', ':'], '_', trim(substr($rows[0], $start, $num - 1 - $start)))),
                    'size' => $num - 1 - $start,
                ];
                $start = $num;
            }
        }
        $response = [];
        foreach (array_splice($rows, 2) as $row) {
            $resp = [];
            if (!trim($row)) continue;
            foreach ($cols as $cell) {
                $resp[$cell['name']] = trim(substr($row, $cell['start'], $cell['size']));
            }
            $response[] = $resp;
        }
        return $response;
    }

    function convertHexToString($string, $trimNulls = false)
    {
        if ($trimNulls) {
            $string = rtrim($string, "0");
        }
        $symbols = explode(":", $string);
        $str = '';
        $char = '';
        foreach ($symbols as $symbol) {
            if (!hexdec($symbol)) continue;
            $char = chr(hexdec($symbol));
            if (!mb_detect_encoding($char, 'Windows-1251', true) && !mb_detect_encoding($char, 'ASCII', true)) {
                continue;
            }
            $str .= $char;
        }
        if (mb_detect_encoding($char, 'ASCII', true)) {
            return iconv("ASCII", "UTF-8//IGNORE", $str,);;
        }
        if (mb_detect_encoding($char, 'Windows-1251', true)) {
            return iconv("Windows-1251", "UTF-8//IGNORE", $str,);
        }
        return '';
    }

    function parseExpandedTable($input)
    {
        $responses = [];
        $r = [];
        if (preg_match('/^\%Error/', trim($input))) {
            throw new Exception("Device returned error - '$input'");
        }
        $lines = explode("\n", trim($input));
        if (count($lines) < 2) {
            throw new Exception("Unknown input - '" . join($lines) . "'");
        }
        foreach ($lines as $line) {
            if (preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                $key = $this->fromCamelCase(str_replace([' ', '-'], '_', trim($m[1])));
                $r[$key] = trim($m[2]);
            } elseif (!trim($line)) {
                if (count($r) > 0) {
                    $responses[] = $r;
                }
                $r = [];
            }
        }
        if (count($r) > 0) {
            $responses[] = $r;
        }
        return $responses;
    }
}