<?php


namespace SwitcherCore\Modules\GCOM;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceCounters extends GCOMAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }


    function getPretty()
    {
        $data = [];
        foreach ($this->response as $name=>$resp) {
            if($resp->error()) {
                throw new \SNMPException($resp->error());
            }
            foreach ($resp->fetchAll() as $r) {
                $iface = $this->parseInterface($this->getOnuXidByOid($r->getOid()));
                $data[$iface['id']]['interface'] = $iface;
                $value = (int)$r->getValue();
                if($value < 0) {
                    $value = + (2147483648 * 2);
                }
                switch ($name) {
                    case 'ont.stat.inOctets': $data[$iface['id']]['in_octets'] = $value; break;
                    case 'ont.stat.outOctets': $data[$iface['id']]['out_octets'] = $value; break;
                    case 'ont.stat.inCrcErrors': $data[$iface['id']]['crc_errors'] = $value; break;
                }
            }
        }
        return array_values($data);
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $ontOids = [
            $this->oids->getOidByName('ont.stat.inOctets'),
            $this->oids->getOidByName('ont.stat.outOctets'),
            $this->oids->getOidByName('ont.stat.inCrcErrors'),
        ];
        if ($filter['interface']) {

            $interface = $this->parseInterface($filter['interface']);
            $data = $this->formatResponse(
                $this->snmp->get(
                    array_map(function ($o) use ($interface) {
                        return \SnmpWrapper\Oid::init($o->getOid() . ".{$interface['xid']}");
                    }, $ontOids)
                )
            );
        } else {
            $data = $this->formatResponse(
                $this->snmp->walk(
                    array_map(function ($o)  {
                        return \SnmpWrapper\Oid::init($o->getOid());
                    }, $oids)
                )
            );
        }
        $this->response = $data;
        return $this;
    }
}

