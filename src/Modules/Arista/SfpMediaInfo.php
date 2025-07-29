<?php

namespace SwitcherCore\Modules\Arista;

use SnmpWrapper\Oid;
use SnmpWrapper\Request\PoollerRequest;
use SwitcherCore\Exceptions\IncompleteResponseException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\General\Switches\AbstractInterfaces;
use SwitcherCore\Modules\General\Switches\FdbDot1Bridge;
use SwitcherCore\Modules\Helper;

class SfpMediaInfo extends AbstractInterfaces
{
    use InterfacesTrait;


    public function run($params = [])
    {
        if($params['interface']) {
            $interfaces = [$this->parseInterface($params['interface'])];
        } else {
            $interfaces = $this->getInterfacesIds();
        }
        $resp = [];
        foreach ($interfaces as $interface) {
            $resp[] = [
                'interface' => $interface,
                'connector_type' => isset($interface['_sfp']['type']) ? $interface['_sfp']['type'] : null,
                'eth_compliance_codes' => isset($interface['_sfp']['media']) ? $interface['_sfp']['media'] : null,
                'vendor_name' =>  null,
                '_model' => isset($interface['_sfp']['model']) ? $interface['_sfp']['model'] : null,
                'part_number' => null,
                'baud_rate' => null,
                'serial_num' => null,
            ];
        }
        $this->response = $resp;
        return $this;
    }

    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}
