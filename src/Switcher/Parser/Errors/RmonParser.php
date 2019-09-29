<?php


namespace SnmpSwitcher\Switcher\Parser\Errors;

use \SnmpSwitcher\Switcher\Parser\AbstractParser;


class RmonParser extends AbstractParser
{
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
        
        return $this;
    }
}