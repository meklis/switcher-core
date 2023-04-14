<?php


namespace SwitcherCore\Modules\Dlink\Link;

use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\Dlink\SwitchesPortAbstractModule;
use SwitcherCore\Modules\Helper;

class NoMediumTypeParser extends SwitchesPortAbstractModule
{
    protected function formate() {
          $link_status = $this->getResponseByName('dlink.PortInfoLinkStatus');
          $nway_status = $this->getResponseByName('dlink.PortInfoNwayStatus');
          $link_state = $this->getResponseByName('dlink.PortCtrlPortAdminState');
          $nway_state = $this->getResponseByName('dlink.PortCtrlPortNwayState');
          $description = $this->getResponseByName('if.Alias');
          $types = $this->getResponseByName('if.Type');

          if($link_state->error()) {
              throw new Exception($link_state->error());
          }
          if($types->error()) {
              throw new Exception($types->error());
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


          $response=[];
          foreach ($link_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid());
              $response["{$port}"]['interface'] = $this->parseInterface($port);
              $response["{$port}"]['medium_type'] = $this->model->getExtraParamByName('medium_type');
              $response["{$port}"]['type'] = null;
              $response["{$port}"]['last_change'] = null;
              $response["{$port}"]['connector_present'] = null;
              $response["{$port}"]['oper_status'] = $d->getParsedValue();
              $response["{$port}"]['description'] = "";
              $response["{$port}"]['admin_state'] = "";
              $response["{$port}"]['nway_status'] = "";
              $response["{$port}"]['address_learning'] = "";
          }

        foreach ($nway_state->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            $response["{$port}"]['admin_state'] = $d->getParsedValue();
        }

          foreach ($link_state->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid());
              if($d->getParsedValue() == 'Disabled') {
                  $response[$port]['admin_state'] = $d->getParsedValue();
              }
          }

          foreach ($nway_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid());
              $response[$port]['nway_status'] =  $d->getParsedValue();
          }

        foreach ($description->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($response[$port])) $response[$port]['description'] =  trim($d->getValue(), '\"');
        }
        foreach ($types->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($this->model->getExtra()['ge_ports']) && in_array($port, $this->model->getExtra()['ge_ports'])) {
                $d->setParsed('GE');
            }
            if(isset($response[$port])) $response[$port]['type'] =   $d->getParsedValue();
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
            Oid::init($this->oids->getOidByName('dlink.PortInfoNwayStatus')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortCtrlPortAdminState')->getOid()) ,
            Oid::init($this->oids->getOidByName('dlink.PortCtrlPortNwayState')->getOid()) ,
            Oid::init($this->oids->getOidByName('if.Alias')->getOid()) ,
            Oid::init($this->oids->getOidByName('if.Type')->getOid(), true) ,
        ];
        $this->response = $this->formatResponse($this->snmp->walkBulk($prepared));
        return $this;
    }
}


