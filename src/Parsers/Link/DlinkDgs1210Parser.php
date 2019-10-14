<?php


namespace SwitcherCore\Parsers\Link;

use SwitcherCore\Switcher\Objects\WrappedResponse;
use \SwitcherCore\Parsers\AbstractParser;
use \SwitcherCore\Parsers\ParserInterface;
use \SwitcherCore\Parsers\Helper;

class DlinkDgs1210Parser extends AbstractParser
{
    protected function formate() {
          $nway_status = $this->getResponseByName('dlink.sysPortCtrlOperStatus');
          $link_state = $this->getResponseByName('dlink.sysPortCtrlState');
          $nway_state = $this->getResponseByName('dlink.sysPortCtrlSpeed');
          $description = $this->getResponseByName('if.Alias');
          $medium_type = $this->getResponseByName('dlink.sysPortCtrlMediumType');
          $last_change = $this->getResponseByName('dlink.sysPortLinkChangeTime');

          if($link_state->error()) {
              throw new \Exception($link_state->error());
          }
          if($medium_type->error()) {
              throw new \Exception($medium_type->error());
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
          if($last_change->error()) {
              throw new \Exception($last_change->error());
          }

          $indexMediumType = [];
          foreach ($medium_type->fetchAll() as $d) {
              $indexMediumType[Helper::getIndexByOid($d->getOid())] = $d->getParsedValue();
          }

          $response=[];
          foreach ($nway_status->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $status = 'Down';
              if($d->getParsedValue() != 'Down') {
                  $status = 'Up';
              }
              $response["{$port}-{$type}"]['port'] = $port;
              $response["{$port}-{$type}"]['medium_type'] = $type;
              $response["{$port}-{$type}"]['type'] = 'GE';
              $response["{$port}-{$type}"]['last_change'] = null;
              $response["{$port}-{$type}"]['connector_present'] = null;
              $response["{$port}-{$type}"]['oper_status'] = $status;
              $response["{$port}-{$type}"]['description'] = null;
              $response["{$port}-{$type}"]['admin_state'] = null;
              $response["{$port}-{$type}"]['nway_status'] = $d->getParsedValue();
              $response["{$port}-{$type}"]['address_learning'] = null;
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
          foreach ($last_change->fetchAll() as $d) {
              $port = Helper::getIndexByOid($d->getOid(),1);
              $type = $indexMediumType[Helper::getIndexByOid($d->getOid())];
              $response["{$port}-{$type}"]['last_change'] =  $d->getParsedValue();
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
            $this->oidsCollector->getOidByName('dlink.sysPortCtrlMediumType')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.sysPortCtrlSpeed')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.sysPortCtrlOperStatus')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.sysPortCtrlState')->getOid() ,
            $this->oidsCollector->getOidByName('dlink.sysPortLinkChangeTime')->getOid() ,
            $this->oidsCollector->getOidByName('if.Alias')->getOid() ,
        ];
        $this->response = $this->formatResponse($this->walker->walkBulk($prepared));
        return $this;
    }
}


