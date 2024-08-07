<?php


namespace SwitcherCore\Modules\CData\FD16xxV3;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * Class OntUniInformation
 * @package SwitcherCore\Modules\CData\FD16xxV3
 */
class OntUniPortsStatus extends CDataAbstractModuleFD16xxV3
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter); // TODO: Change the autogenerated stub
    }

    public function getUniDataByConsole($iface){
        if ($iface['type'] != 'ONU') {
            throw new \Exception("Interface type {$iface['type']} should be onu");
        }


        $this->console->exec("interface {$iface['_type']} 0/0");
        $resp = $this->console->exec("show ont port state {$iface['_port']} {$iface['_onu']} eth all");
        $response = [];
        $lines = explode("\n", $resp);

        for($i=0;$i<count($lines);$i++){
            $parts = preg_split('/\s+/', trim($lines[$i]));
            if(count($parts) > 1){
                $response[] = $parts;
            }
        }

        return $this->wrapConsoleTable($response);
    }

    public function wrapConsoleTable($response)
    {
        if(count($response) < 2) {
            return null;
        }
        $rows = [];
        foreach ($response[0] as $key => $value) {
            switch ($value) {
                case 'Port': $rows[$key] = 'num'; break;
                case 'Type': $rows[$key] = 'type'; break;
                case 'Speed(Mbps)': $rows[$key] = 'speed'; break;
                case 'Duplex': $rows[$key] = 'duplex'; break;
                case 'Link-State': $rows[$key] = 'status'; break;
            }
        }
        unset($response[0]);
        $unis = [];
        foreach ($response as $_id=>$row) {
            foreach ($row as $cellID=>$rw) {
                if(!isset($rows[$cellID])) continue;
                if($rows[$cellID] == 'status') {
                    $rw = ucfirst($rw);
                }
                if($rows[$cellID] == 'duplex') {
                    $rw = ucfirst($rw);
                }
                if($rows[$cellID] == 'speed' && $rw == '1000') {
                    $rw = '1G';
                }
                if($rw == '-') {
                    $rw = null;
                }
                $unis[$_id][$rows[$cellID]] = $rw;
            }
        }
        return $unis;
    }


    function getPretty()
    {
        return $this->response;
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oids = [];
        if(!$filter['interface']) {
           throw new  \Exception("Interface not specified");
        }
        $interface = $this->parseInterface($filter['interface']);
        $this->response = [];
        $statuses = $this->getUniDataByConsole($interface);
        if(!$statuses) {
            $this->response = null;
            return  $this;
        }
        $this->fillAdminStatus($statuses, $interface);
        $this->response[] = [
            'interface' => $interface,
            'unis' => array_values($statuses),
        ];

        return $this;
    }

    public function fillAdminStatus(&$statuses, $interface)
    {
        $resp = $this->console->exec("show ont port attribute {$interface['_port']} {$interface['_onu']} eth all");
        $lines = explode("\n", $resp);

        for($i=3;$i<count($lines);$i++){
            $parts = preg_split('/\s+/', trim($lines[$i]));
            if(count($parts) > 1){
                $state = 'N/A';
                if($parts[7] == 'enable') {
                    $state = 'Enabled';
                }
                if($parts[7] == 'disable') {
                    $state = 'Disabled';
                }
                $statuses[$parts[3]]['admin_state'] = $state;
            }
        }
        return  $statuses;
    }
}

