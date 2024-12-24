<?php

namespace SwitcherCore\Modules\DellSwitch;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class BgpPeers extends AbstractModule
{
    public function run($params = [])
    {
        $oids = $this->oids->getOidsByRegex('^bgp\.peer\..*');
        $this->response = $this->formatResponse($this->snmp->walk($oids));

        foreach ($this->response as $data) {
            if($data->error()) {
                throw new \SNMPException($data->error());
            }
        }
        return $this;
    }

    public function getPretty()
    {
        $bgpPeers = [];
        foreach ($this->response as $key => $data) {
            $name = str_replace("bgp.peer.", "", $key);
            foreach ($data->fetchAll() as $d) {
                $idx =
                    Helper::getIndexByOid($d->getOid(), 3) . "." .
                    Helper::getIndexByOid($d->getOid(), 2) . "." .
                    Helper::getIndexByOid($d->getOid(), 1) . "." .
                    Helper::getIndexByOid($d->getOid()) ;
                $bgpPeers[$idx][$name] = $d->getParsedValue();
            }
        }
        return array_values($bgpPeers);
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

}