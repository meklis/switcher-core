<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTableConsole extends BDcomAbstractModule
{

    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;


    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getPretty()
    {
        return $this->response;
    }


    public function run($filter = [])
    {
        $response = [];
        $data = "";
       if($filter['interface']) {
           $iface = $this->parseInterface($filter['interface']);
           $data = $this->console->exec("show mac address-table interface {$iface['name']}");
       } elseif ($filter['mac']) {
           throw new \Exception("Searching by mac-address not supported yet.");
       } elseif ($filter['vlan_id']) {
           $data = $this->console->exec("show mac address-table vlan {$filter['vlan_id']}");
       } else {
           $data = $this->console->exec("show mac address-table");
       }
       foreach (explode("\n",$data) as $dt) {
           if(preg_match('/^([0-9]{1,5})\s*([[:xdigit:]]{4}\.[[:xdigit:]]{4}\.[[:xdigit:]]{4})\s*(DYNAMIC|STATIC)\s*?(\S*)$/', $dt, $m)) {
               $uniSearch = explode("-", $m[4]);
               $iface = $this->parseInterface($uniSearch[0]);
               $response[] = [
                   'interface' => $iface,
                   '_virtual_port' => isset($uniSearch[1]) ?  $uniSearch[1] : null,
                   'vlan_id' => (int)$m[1],
                   'mac_address' => Helper::formatMac($m[2]),
               ];
           }
       }
       $this->response = $response;
       return $this;
    }
}

