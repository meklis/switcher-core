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
        // TODO: Implement walk() method.
        return $this;
    }
}