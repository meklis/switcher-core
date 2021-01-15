<?php


namespace SwitcherCore\Modules\Telnet\ZTE\C300Series;


use SwitcherCore\Modules\AbstractModule;

abstract class C300ModuleAbstract extends AbstractModule
{
    public function parsePortByName($name)
    {
        if (preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3})/', $name, $matches)) {
            $onu = null;
            if ($matches[2] == 'onu' && preg_match('/^(gpon|epon)-(onu|olt)_([0-9])\/([0-9]{1,3})\/([0-9]{1,3}):([0-9]{1,3})/', $name, $m)) {
                $onu = $m[6];
            }
            return [
                'technology' => $matches[1],
                'is_onu' => $matches[2] === 'onu',
                'is_port' => $matches[2] === 'olt',
                'shelf' => $matches[3],
                'slot' => $matches[4],
                'port' => $matches[5],
                'onu' => $onu,
            ];
        }
        throw new \InvalidArgumentException("Error parse port with name '$name'");
    }
    protected function exec($command) {
        $response = $this->obj->telnet->exec($command);
        if(!trim($response)) return true;
        if(preg_match('/^\%Info/', $response)) return  true;
        if(preg_match('/\[Successful\]/', $response)) return  true;
        if(preg_match('/\[OK\]/', $response)) return  true;
        if(preg_match('/Invalid input detected/', $response)) throw new \Exception("Invalid input detected for command '$command'");
        throw new \Exception("Unknown response for command '$command' - \n>>>{$response}<<<");
    }
    public function macTo6octets($mac) {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if(count($m) < 12) {
            throw new \Exception("Received incorrect MAC-address");
        }
        return strtoupper("{$m[0]}{$m[1]}:{$m[2]}{$m[3]}:{$m[4]}{$m[5]}:{$m[6]}{$m[7]}:{$m[8]}{$m[9]}:{$m[10]}{$m[11]}");
    }
    public function macTo3octets($mac) {
        $m = str_split(str_replace(["-", ".", ":", " "], "", trim($mac)));
        if(count($m) < 12) {
            throw new \Exception("Received incorrect MAC-address");
        }
        return strtolower("{$m[0]}{$m[1]}{$m[2]}.{$m[3]}{$m[4]}{$m[5]}.{$m[6]}{$m[7]}{$m[8]}.{$m[9]}{$m[10]}{$m[11]}");
    }
}