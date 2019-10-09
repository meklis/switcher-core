<?php


namespace SnmpSwitcher\Switcher\Parser\Link;

use SnmpSwitcher\Switcher\Objects\WrappedResponse;
use \SnmpSwitcher\Switcher\Parser\AbstractParser;
use \SnmpSwitcher\Switcher\Parser\ParserInterface;
use \SnmpSwitcher\Switcher\Parser\Helper;

class DlinkParser extends AbstractParser
{
    private $indexesPort = [];
    protected function formate() {
          $link_status = $this->getResponseByName('dlink.PortInfoLinkStatus');
          $nway_status = $this->getResponseByName('dlink.PortInfoNwayStatus');
          $link_state = $this->getResponseByName('dlink.PortCtrlPortAdminState');
          $nway_state = $this->getResponseByName('dlink.PortCtrlPortNwayState');
          $addr_learning = $this->getResponseByName('dlink.PortCtrlAddressLearning');
          $description = $this->getResponseByName('if.Alias');

          if($link_state->error()) {
              throw new \Exception($link_state->error());
          }
          if($link_status->error()) {
              throw new \Exception($link_status->error());
          }
          if($nway_status->error()) {
              throw new \Exception($nway_status->error());
          }
          if($nway_state->error()) {
              throw new \Exception($nway_state->error());
          }
          if($description->error()) {
              throw new \Exception($description->error());
          }
          if($addr_learning->error()) {
              throw new \Exception($addr_learning->error());
          }
          $response=[];
          $_type = function ($oid) {
              if(Helper::getIndexByOid($oid) == 100) {
                  return "Cooper";
              }  else {
                  return "Fiber";
              }
          };
          foreach ($link_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $_type($d->getOid());
              $response["{$port}-{$type}"]['port'] = $port;
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
            $type = $_type($d->getOid());
                $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
        }

          foreach ($link_state->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $_type($d->getOid());
              if($d->getParsedValue() == 'Disabled') {
                  $response["{$port}-{$type}"]['admin_state'] = $d->getParsedValue();
              }
          }

          foreach ($nway_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $_type($d->getOid());
              $response["{$port}-{$type}"]['nway_status'] =  $d->getParsedValue();
          }
          foreach ($addr_learning->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $_type($d->getOid());
              $response["{$port}-{$type}"]['address_learning'] =  $d->getParsedValue();
          }

        foreach ($description->fetchAll() as $d) {
            $port = Helper::getIndexByOid($d->getOid());
            if(isset($response["{$port}-Cooper"])) $response["{$port}-Cooper"]['description'] =   $d->getValue();
            if(isset($response["{$port}-Fiber"])) $response["{$port}-Fiber"]['description'] =   $d->getValue();
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
    public function walk($filter = [])
    {
        $prepared = [
            $this->oidsCollector->getOidByName('dlink.PortInfoLinkStatus')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.PortInfoNwayStatus')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.PortCtrlPortAdminState')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.PortCtrlPortNwayState')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.PortCtrlAddressLearning')->getOid() ,
            $this->oidsCollector->getOidByName('if.Alias')->getOid() ,
        ];
        $this->response = $this->formatResponse($this->walker->walkBulk($prepared));
        return $this;
    }
}


