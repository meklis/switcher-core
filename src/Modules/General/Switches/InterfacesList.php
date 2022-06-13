<?php

namespace SwitcherCore\Modules\General\Switches;

abstract class InterfacesList extends AbstractInterfaces
{
    public function run($params = [])
    {
       return $this;
    }

    public function getPretty()
    {
        return null;
    }

    public function getPrettyFiltered($filter = [])
    {
        $ifaces =  array_values($this->getInterfacesIds());
        if($filter['interface']) {
            $iface = $this->parseInterface($filter['interface']);
            $ifaces = array_filter($ifaces, function ($e) use ($iface) {
               return $e['id'] == $iface['id'];
            });
        }
        return $ifaces;
    }

}