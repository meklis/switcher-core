<?php


namespace SwitcherCore\Modules\ZTE\C600Series;

use \Exception;
use SwitcherCore\Modules\Helper;

class OnuIpHost extends ZteConsoleOntInfo {
    protected function getInfoGPON($interface) {
        $key = 'ZteOntIpHost_' . $interface;
        if ($response = $this->getCache($key, true)) {
            return $response;
        }

        $input = $this->getModule('console_command')
            ->run([ 'command' => 'show gpon remote-onu ip-host ' . $interface ])->getPretty();
        
        if(!$input) throw new Exception('Console command failed for interface ' . $interface);
        $hosts = explode("\n\n", $input['output']);
        $output = [];

        foreach($hosts as $host) {
            $info = explode("\n", $host);
            $host_info = array_shift($info);
            if(preg_match('/^Host ID:\s*(\d{1,3})$/', trim($host_info), $m)) {
                $host_id = $m[1];
                $output[$host_id]['host_id'] = $host_id;

                foreach($info as $line) {
                    if (preg_match('/^(.*?)\:(.*)$/', trim($line), $m)) {
                        $arr_key = str_replace(" ", "_", strtolower(trim($m[1])));
                        $value = trim($m[2]);
                        if($arr_key === 'mac_address') $value = Helper::formatMac($value);
                        $output[$host_id][$arr_key] = $value;
                    }
                }

            } else {
                throw new Exception("Wrong info format for command 'show gpon remote-onu ip-host " . $interface . "'");
            }
        }
        $response['data'] = array_values($output);

        $this->setCache($key, $response, 600, true);
        return $response;
    }
}