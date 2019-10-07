<?php


namespace SnmpSwitcher\Switcher\Parser\CableDiag;

use \SnmpSwitcher\Switcher\Parser\AbstractParser;


class OldDlinkParser extends AbstractParser
{
    function parse($filter = [])
    {
        parent::parse();
    }

    function getRaw()
    {
        // TODO: Implement getRaw() method.
    }

    function getPretty()
    {
        // TODO: Implement getSwitchData() method.
    }
    function getPrettyFiltered($filter = [])
    {
        // TODO: Implement getPrettyFiltered() method.
    }

    public function walk($filter = [])
    {
        //Prepare ports and pairs list
        $prepared = $this->formatResponse($this->walker->walk([
          $this->oidsCollector->getOidByName('if.Type')->getOid(),
        ]));
        $ports_list = [];

        print_r($prepared);
        return $this;
    }
}