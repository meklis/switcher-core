<?php


namespace SwitcherCore\Modules\ZTE\C600Series;


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
                    '_onu' => null,
                    '_pon_max_ont_size' => in_array($m[1], ['epon', 'gpon']) ? 128 : null,
                    '_technology' => in_array($m[1], ['epon', 'gpon']) ? $m[1] : null,
                    '_oid_id' => null,
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
                    '_onu' => null,
                    '_pon_max_ont_size' => in_array($m[1], ['epon', 'gpon']) ? 128 : null,
                    '_technology' => in_array($m[1], ['epon', 'gpon']) ? $m[1] : null,
                    '_oid_id' =>  null,
                ];
            }
        }
        $data['names'] = $response;
        foreach ($response as  $val) {
            $data['id'][$val['id']] = $val;
            $data['xid'][$val['_xid']] = $val;
        }
        $this->_xidInterfaces = $data;
        $this->setCache('xid_interfaces', $data, 300, true);
        return $data;
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

    public function parseInterface($name)
    {
        $xidList = $this->listInterfacesByXidNames();
        //Попытка распарсить интерфейс PON по его имени
        $iface = null;
        $ontNum = null;
        if(is_string($name) && preg_match('/^.*?([0-9])\/([0-9]{1,3})\/([0-9]{1,3})/', $name, $m)) {
            if(!isset($xidList['names']["{$m[1]}/{$m[2]}/{$m[3]}"])) {
                throw new \Exception("Error parse port with name={$name}, searching key={$m[1]}/{$m[2]}/{$m[3]}");
            }
            $iface = $xidList['names']["{$m[1]}/{$m[2]}/{$m[3]}"];
        }
        if(is_string($name) && preg_match('/^.*?([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})$/', $name, $m)) {
            if(!isset($xidList['names']["{$m[1]}/{$m[2]}/{$m[3]}"])) {
                throw new \Exception("Error parse port with name={$name}, searching key={$m[1]}/{$m[2]}/{$m[3]}");
            }
            $iface = $xidList['names']["{$m[1]}/{$m[2]}/{$m[3]}"];
            $ontNum = (int)$m[4];
        } elseif(is_numeric($name) && isset($xidList['id'][$name])) {
            $iface = $xidList['id'][$name];
        } elseif (is_numeric($name) && isset($xidList['xid'][$name])) {
            $iface = $xidList['xid'][$name];
        } elseif (preg_match('/^([0-9]{1,10})\.([0-9]{1,3})$/', trim($name), $m)) {

            if(isset($xidList['xid'][$m[1]])) {
                $iface = $xidList['xid'][$m[1]];
                $ontNum = (int)$m[2];
            }
        }  else {
            ksort($xidList['id']);
            foreach ($xidList['id'] as $l) {
                if($l['id'] + 256 > $name && $name > $l['id']) {
                    $iface = $l;
                    $ontNum = $name - $l['id'];
                    break;
                }
            }
        }

        if(!$iface) {
            throw new \Exception("Error parse interface with name=$name");
        }

        if($ontNum) {
            $iface['_onu'] = $ontNum;
            $iface['_oid_id'] = "{$iface['_xid']}.$ontNum";
            $iface['parent'] = $iface['id'];
            $iface['id'] += $ontNum;
            $iface['type'] = "ONU";
            $iface['name'] = "{$iface['_technology']}_onu-{$iface['_shelf']}/{$iface['_slot']}/{$iface['_port']}:{$ontNum}";
        }
        return $iface;
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