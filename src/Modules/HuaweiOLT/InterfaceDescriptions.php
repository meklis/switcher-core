<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\SnmpResponse;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class InterfaceDescriptions extends HuaweiOLTAbstractModule
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
        foreach ($this->response as $name=>$wrapped) {
            if($wrapped->error()) {
                throw new \SNMPException($wrapped->error());
            }
            foreach ($wrapped->fetchAll() as $resp) {
                try {
                    if (strpos($name, 'ont.') !== false) {
                        $iface = $this->findIfaceByOid($resp->getOid());
                    } else {
                        $iface = $this->parseInterface(Helper::getIndexByOid($resp->getOid()));
                    }
                    $d = [
                        'interface' => $iface,
                        'description' => $resp->getValue(),
                    ];
                    if (!$d['interface']['id']) continue;
                    $data[] = $d;
                } catch (\Exception $e) {
                    $this->logger->error("Error get interface description for interface:" . $e->getMessage());
                }
            }
        }
        return $data;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            if ($interface['type'] === 'ONU') {
                $oids = [\SnmpWrapper\Oid::init($this->oids->getOidByName("ont.{$interface['_technology']}.config.description")->getOid() . ".{$interface['xid']}"),];
            } else {
                $oids = [\SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid() . ".{$interface['xid']}"),];
            }

            $this->response = $this->formatResponse(
                $this->snmp->get($oids)
            );
            return $this;
        }
        if ($filter['interface_type'] === 'ONU') {
            $oids = [];
            if($this->isHasGponIfaces()) $oids[] = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.gpon.config.description')->getOid());
            if($this->isHasEponIfaces()) $oids[] = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.epon.config.description')->getOid());

        } elseif ($filter['interface_type'] === 'PHYSICAL') {
            $oids =  [
                \SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid()),
            ];
        } else {
            $oids =  [\SnmpWrapper\Oid::init($this->oids->getOidByName('if.Alias')->getOid())];
            if($this->isHasGponIfaces()) $oids[] = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.gpon.config.description')->getOid());
            if($this->isHasEponIfaces()) $oids[] = \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.epon.config.description')->getOid());
        }
        $this->response = $this->formatResponse(
            $this->snmp->walk($oids)
        );
        return $this;
    }
}

