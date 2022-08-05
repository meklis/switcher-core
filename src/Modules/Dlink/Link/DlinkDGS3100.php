<?php


namespace SwitcherCore\Modules\Dlink\Link;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class DlinkDGS3100 extends SwitchesPortAbstractModule
{
    protected function formate() {
          $snmp_high_speed = !$this->response['if.HighSpeed']->error() ? $this->response['if.HighSpeed']->fetchAll() : [];
          $snmp_last_change = !$this->response['if.LastChange']->error() ? $this->response['if.LastChange']->fetchAll() : [];
          $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
          $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];
          $snmp_duplex = !$this->response['if.StatsDuplexStatus']->error() ? $this->response['if.StatsDuplexStatus']->fetchAll() : [];
          $descr = !$this->response['if.Alias']->error() ? $this->response['if.Alias']->fetchAll() : [];

          $indexes = [];
          foreach ($this->getIndexes() as $index=>$port) {
              if(strpos($port['name'], 'ch') !== false) continue;
              $indexes[$index]['interface'] = $port;
              $indexes[$index]['description'] = null;
              $indexes[$index]['oper_status'] = null;
              $indexes[$index]['nway_status'] = null;
              $indexes[$index]['admin_state'] = null;
              $indexes[$index]['last_change'] = null;
              $indexes[$index]['id'] = $port;
              $indexes[$index]['extra'] = [];
          }

          foreach ($snmp_high_speed as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
              $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] =  $index->getParsedValue();
          }
          foreach ($descr as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
              $indexes[Helper::getIndexByOid($index->getOid())]['description'] =  $index->getValue();
          }
          foreach ($snmp_duplex as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
                if($index->getParsedValue() == 'Down') {
                    $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = $index->getParsedValue();
                } else {
                    $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] .= "-" . $index->getParsedValue();
                }
          }

          foreach ($snmp_oper_status as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
              $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] =  $index->getParsedValue();
          }
          foreach ($snmp_admin_status as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
                $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] =  $index->getParsedValue();
          }
          foreach ($snmp_last_change as $index) {
              if(!isset($indexes[Helper::getIndexByOid($index->getOid())])) continue;
              $indexes[Helper::getIndexByOid($index->getOid())]['last_change'] =  $index->getValueAsTimeTicks();
          }
          return $indexes;
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();
        if($filter['type']) {
            $types = explode(",", $filter['type']);
            foreach ($response as $num => $resp) {
                if(!isset($resp['type']))  {
                    unset($response[$num]);
                    continue;
                }
                if (!in_array($resp['type'], $types)) {
                    unset($response[$num]);
                }
            }
        }
        if($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($response as $num=>$resp) {
                if(!isset($resp['interface']))  {
                    unset($response[$num]);
                    continue;
                }
                if($interface['id'] != $resp['interface']['id']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }
    public function run($filter = [])
    {
        $indexes = $this->getIndexes();

        $data = [
            $this->oids->getOidByName('if.HighSpeed')->getOid() ,
            $this->oids->getOidByName('if.LastChange')->getOid(),
            $this->oids->getOidByName('if.OperStatus')->getOid(),
            $this->oids->getOidByName('if.AdminStatus')->getOid(),
            $this->oids->getOidByName('if.StatsDuplexStatus')->getOid(),
            $this->oids->getOidByName('if.Alias')->getOid(),
        ];

        if ($filter['interface']) {
            $interface = $this->parseInterface($filter['interface']);
            foreach ($data as $num=>$d) {
                $data[$num] .= ".{$interface['id']}";
            }
        }
        $oidObjects = [];
        foreach ($data as $oid) {
            $oidObjects[] = Oid::init($oid);
        }
        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}

