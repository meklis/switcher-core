<?php


namespace SwitcherCore\Modules\Dlink\Link;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Trap;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class DlinkParser extends SwitchesPortAbstractModule
{
    function trap(Trap $trap, $data)
    {
        $data = $this->run(['interface' => $data['interface']['id']])->getPrettyFiltered(['interface' => $data['interface']['id']]);
        return $data;
    }
    protected function formate() {
          $link_status = $this->getResponseByName('dlink.PortInfoLinkStatus');
          $nway_status = $this->getResponseByName('dlink.PortInfoNwayStatus');
          $link_state = $this->getResponseByName('dlink.PortCtrlPortAdminState');
          $nway_state = $this->getResponseByName('dlink.PortCtrlPortNwayState');
          $addr_learning = $this->getResponseByName('dlink.PortCtrlAddressLearning');
          $description = $this->getResponseByName('if.Alias');
          $medium_type = $this->getResponseByName('dlink.PortInfoMediumType');
          $types = $this->getResponseByName('if.Type');

          if($link_state->error()) {
              throw new Exception($link_state->error());
          }
          if($types->error()) {
              throw new Exception($types->error());
          }
          if($medium_type->error()) {
              throw new Exception($medium_type->error());
          }
          if($link_status->error()) {
              throw new Exception($link_status->error());
          }
          if($nway_status->error()) {
              throw new Exception($nway_status->error());
          }
          if($nway_state->error()) {
              throw new Exception($nway_state->error());
          }
          if($description->error()) {
              throw new Exception($description->error());
          }
          if($addr_learning->error()) {
              throw new Exception($addr_learning->error());
          }

          $indexMediumType = [];
          foreach ($medium_type->fetchAll() as $d) {
              $indexMediumType[Helper::getIndexByOid($d->getOid())] = $d->getParsedValue();
          }

          $response=[];
          foreach ($link_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['interface'] = $this->parseInterface($port);
              $response["{$port}-{$type}"]['medium_type'] = $type;
              $response["{$port}-{$type}"]['type'] = null;
              $response["{$port}-{$type}"]['last_change'] = null;
              $response["{$port}-{$type}"]['connector_present'] = null;
              $response["{$port}-{$type}"]['oper_status'] = $d->getParsedValue();
              $response["{$port}-{$type}"]['description'] = "";
              $response["{$port}-{$type}"]['admin_state'] = "";
              $response["{$port}-{$type}"]['nway_status'] = "";
              $response["{$port}-{$type}"]['address_learning'] = "";
          }

        foreach ($nway_state->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid(), 1);
            $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
                $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
        }

          foreach ($link_state->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              if($d->getParsedValue() == 'Disabled') {
                  $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
              }
          }

          foreach ($nway_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['nway_status'] =  $d->getParsedValue();
          }
          foreach ($addr_learning->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['address_learning'] =  $d->getParsedValue();
          }

        foreach ($description->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($response["{$port}-Cooper"])) $response["{$port}-Cooper"]['description'] =  trim($d->getValue(), '\"');
            if(isset($response["{$port}-Fiber"])) $response["{$port}-Fiber"]['description'] =  trim($d->getValue(), '\"');
        }
        foreach ($types->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($this->model->getExtra()['ge_ports']) && in_array($port, $this->model->getExtra()['ge_ports'])) {
                $d->setParsed('GE');
            }
            if(isset($response["{$port}-Cooper"])) $response["{$port}-Cooper"]['type'] =   $d->getParsedValue();
            if(isset($response["{$port}-Fiber"])) $response["{$port}-Fiber"]['type'] =   $d->getParsedValue();
        }
          return $response;
    }
    function getPretty()
    {
        return $this->formate();
    }
    function getPrettyFiltered($filter = [])
    {
        Helper::prepareFilter($filter);
        $response = $this->formate();

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
        $prepared = [
            Oid::init($this->oids->getOidByName('dlink.PortInfoLinkStatus')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortInfoMediumType')->getOid(), true) ,
            Oid::init($this->oids->getOidByName('dlink.PortInfoNwayStatus')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortCtrlPortAdminState')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortCtrlPortNwayState')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortCtrlAddressLearning')->getOid()) ,
            Oid::init($this->oids->getOidByName('if.Alias')->getOid()) ,
            Oid::init($this->oids->getOidByName('if.Type')->getOid(), true) ,
        ];
        $this->response = $this->formatResponse($this->snmp->walkBulk($prepared));
        return $this;
    }
}


