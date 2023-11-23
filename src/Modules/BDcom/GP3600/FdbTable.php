<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;

class FdbTable extends BDcomAbstractModule
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
       if($filter['interface']) {
           $iface = $this->parseInterface($filter['interface']);
           $this->checkSnmpRespError($this->snmp->set(
               \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.setIfIndex')->getOid())
               ->setType("Integer")
               ->setValue($iface['xid'])
           ));
           $this->checkSnmpRespError($this->snmp->set(
               \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.actionSearch')->getOid())
               ->setType("Integer")
               ->setValue(3)
           ));
           try {
               $response = $this->fetchFDB();
           } catch (\Exception $e) {
               if(strpos($e->getMessage(), "No Such Instance currently exists at this OID") === false) {
                   throw $e;
               }
           }
       } elseif ($filter['mac']) {
           throw new \Exception("Searching by mac-address not supported yet.");
       } elseif ($filter['vlan_id']) {
           $this->checkSnmpRespError($this->snmp->set(
               \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.setVid')->getOid())
                   ->setType("Integer")
                   ->setValue($filter['vlan_id'])
           ));
           $this->checkSnmpRespError($this->snmp->set(
               \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.actionSearch')->getOid())
                   ->setType("Integer")
                   ->setValue(4)
           ));
           try {
           $response = $this->fetchFDB();
           } catch (\Exception $e) {
               if(strpos($e->getMessage(), "No Such Instance currently exists at this OID") === false) {
                   throw $e;
               }
           }
       } else {
           $vlans = $this->getModule('vlans')->run()->getPretty();
           foreach ($vlans as $vlan) {
               $this->checkSnmpRespError($this->snmp->set(
                   \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.setVid')->getOid())
                       ->setType("Integer")
                       ->setValue($vlan['vlan_id'])
               ));
               $this->checkSnmpRespError($this->snmp->set(
                   \SnmpWrapper\Oid::init($this->oids->getOidByName('fdb.actionSearch')->getOid())
                       ->setType("Integer")
                       ->setValue(4)
               ));
               try {
                   $response = array_merge($response, $this->fetchFDB());
               } catch (\Exception $e) {
                   if(strpos($e->getMessage(), "No Such Instance currently exists at this OID") === false) {
                       throw $e;
                   }
               }
           }
       }
       $this->response = $response;
       return $this;
    }
    function fetchFDB() {
        $oids = array_map(function ($oid) {
            return \SnmpWrapper\Oid::init($oid->getOid());
        }, $this->oids->getOidsByRegex('^fdb.getResult'));
        $result = [];
        foreach ($this->formatResponse($this->snmp->walkNext($oids)) as $name => $res) {
            if($res->error()) {
                throw new \Exception("Error fetch FDB - {$res->error()}");
            }
            foreach ($res->fetchAll() as $resp) {
                $index = Helper::getIndexByOid($resp->getOid());
                switch ($name) {
                    case 'fdb.getResultIf':
                        try {
                            $result[$index]['interface'] = $this->parseInterface($resp->getValue());
                        } catch (\Exception $e) {

                        }
                        break;
                    case 'fdb.getResultVp':
                        $result[$index]['_virtual_port'] = (int)$resp->getValue();
                        break;
                    case 'fdb.getResultVlan':
                        $result[$index]['vlan_id'] = (int)$resp->getValue();
                        break;
                    case 'fdb.getResultMacAddr':
                        $result[$index]['mac_address'] = $resp->getHexValue();
                        break;
                }
            }
        }
        return array_values(array_filter($result, function ($e) {
            return isset($e['interface']);
        }));
    }

}

