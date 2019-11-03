<?php


namespace SwitcherCore\Modules\Snmp\Link;

use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

;

class DefaultParser extends AbstractModule
{
    private $indexesPort = [];
    protected function formate() {
          $snmp_high_speed = !$this->response['if.HighSpeed']->error() ? $this->response['if.HighSpeed']->fetchAll() : [];
          $snmp_type = !$this->response['if.Type']->error() ? $this->response['if.Type']->fetchAll() : [];
          $snmp_last_change = !$this->response['if.LastChange']->error() ? $this->response['if.LastChange']->fetchAll() : [];
          $snmp_oper_status = !$this->response['if.OperStatus']->error() ? $this->response['if.OperStatus']->fetchAll() : [];
          $snmp_admin_status = !$this->response['if.AdminStatus']->error() ? $this->response['if.AdminStatus']->fetchAll() : [];
          $snmp_connector = !$this->response['if.ConnectorPresent']->error() ? $this->response['if.ConnectorPresent']->fetchAll() : [];
          $snmp_duplex = !$this->response['if.StatsDuplexStatus']->error() ? $this->response['if.StatsDuplexStatus']->fetchAll() : [];
          $descr = !$this->response['if.Alias']->error() ? $this->response['if.Alias']->fetchAll() : [];

          $indexes = [];
          foreach ($this->getIndexes() as $index=>$port) {

              $indexes[$index]['port'] = $port;
              $indexes[$index]['medium_type'] = null;
              $indexes[$index]['address_learning'] = null;
              $indexes[$index]['description'] = null;
              $indexes[$index]['oper_status'] = null;
              $indexes[$index]['nway_status'] = null;
              $indexes[$index]['admin_state'] = null;
              $indexes[$index]['admin_state'] = null;
              $indexes[$index]['last_change'] = null;
              $indexes[$index]['connector_present'] = null;
              $indexes[$index]['id'] = $port;
              $indexes[$index]['extra'] = [];
          }

          foreach ($snmp_high_speed as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] =  $index->getValue();
          }
          foreach ($descr as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['description'] =  $index->getValue();
          }
          foreach ($snmp_duplex as $index) {
                if($index->getParsedValue() == 'Down') {
                    $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] = $index->getParsedValue();
                } else {
                    $indexes[Helper::getIndexByOid($index->getOid())]['nway_status'] .= "-" . $index->getParsedValue();
                }
          }

          foreach ($snmp_oper_status as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['oper_status'] =  $index->getParsedValue();
          }
          foreach ($snmp_admin_status as $index) {
                $indexes[Helper::getIndexByOid($index->getOid())]['admin_state'] =  $index->getParsedValue();
          }
          foreach ($snmp_type as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['type'] =  $index->getParsedValue();
          }
          foreach ($snmp_last_change as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['last_change'] =  $index->getValueAsTimeTicks();
          }
          foreach ($snmp_connector as $index) {
              $indexes[Helper::getIndexByOid($index->getOid())]['connector_present'] =  $index->getParsedValue();
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
        if($filter['port']) {
            foreach ($response as $num=>$resp) {
                if(!isset($resp['port']))  {
                    unset($response[$num]);
                    continue;
                }
                if($filter['port'] != $resp['port']) {
                    unset($response[$num]);
                }
            }
        }
        return array_values($response);
    }
    public function run($filter = [])
    {
        $indexes = [];
        foreach ($this->getIndexes() as $index=>$port) {
         $indexes[$port] = $index;
        }

        $data = [
            $this->oidsCollector->getOidByName('if.HighSpeed')->getOid() ,
            $this->oidsCollector->getOidByName('if.Name')->getOid(),
            $this->oidsCollector->getOidByName('if.Type')->getOid(),
            $this->oidsCollector->getOidByName('if.LastChange')->getOid(),
            $this->oidsCollector->getOidByName('if.OperStatus')->getOid(),
            $this->oidsCollector->getOidByName('if.AdminStatus')->getOid(),
            $this->oidsCollector->getOidByName('if.ConnectorPresent')->getOid(),
            $this->oidsCollector->getOidByName('if.StatsDuplexStatus')->getOid(),
            $this->oidsCollector->getOidByName('if.Alias')->getOid(),
        ];

        if ($filter['port']) {
            foreach ($data as $num=>$d) {
                $data[$num] .= ".{$indexes[$filter['port']]}";
            }
        }
        $this->response = $this->formatResponse($this->walker->walk($data));
        return $this;
    }
}

