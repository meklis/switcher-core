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
     * @var SnmpResponse[]
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
        foreach ($this->response as $resp) {
            try {
                $d = [
                    'interface' => $this->findIfaceByOid($resp->getOid()),
                    'description' => $resp->getValue(),
                ];
                if (!$d['interface']['id']) continue;
                $data[] = $d;
            } catch (\Exception $e) {
                $this->logger->error("Error get interface description for interface:" . $e->getMessage());
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
            $data = $this->formatResponse(
                $this->snmp->get([
                    \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.config.description')->getOid() . ".{$interface['xid']}"),
                ])
            );
        } else {
            $data = $this->formatResponse(
                $this->snmp->walk(
                    [
                        \SnmpWrapper\Oid::init($this->oids->getOidByName('ont.config.description')->getOid()),
                    ]
                )
            );
        }
        $resp = $this->getResponseByName('ont.config.description', $data);
        if($resp->error()) {
            throw new \Exception($resp->error());
        }
        $this->response = $this->getResponseByName('ont.config.description', $data)->fetchAll();
        return $this;
    }
}
