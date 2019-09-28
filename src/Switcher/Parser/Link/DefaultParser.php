<?php


namespace SnmpSwitcher\Switcher\Parser\Link;

use SnmpSwitcher\Switcher\Objects\WrappedResponse;
use \SnmpSwitcher\Switcher\Parser\AbstractParser;
use \SnmpSwitcher\Switcher\Parser\ParserInterface;

class DefaultParser extends AbstractParser
{
    protected function formate() {
      $ports = [];
      if(!$this->response['if.Index']->error()) {
          $data = $this->response['if.Index'];
          foreach ($data->fetchAll() as $resp) {
        }
      }
    }
    function getPretty()
    {
        $formated = $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        // TODO: Implement getPrettyFiltered() method.
    }
    public function walk($filter = [])
    {
        $data = [
            $this->oidsCollector->getOidByName('if.Index')->getOid()  ,
            $this->oidsCollector->getOidByName('if.HighSpeed')->getOid() ,
            $this->oidsCollector->getOidByName('if.Name')->getOid(),
            $this->oidsCollector->getOidByName('if.Type')->getOid(),
            $this->oidsCollector->getOidByName('if.LastChange')->getOid(),
            $this->oidsCollector->getOidByName('if.OperStatus')->getOid(),
            $this->oidsCollector->getOidByName('if.AdminStatus')->getOid(),
        ];
        if ($filter['port']) {
            foreach ($data as $d) {
                $d .= ".{$filter['port']}";
            }
        }
        $this->response = $this->formatResponse($this->walker->walk($data));

        return $this;
    }
}

