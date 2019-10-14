<?php


namespace SwitcherCore\Parsers\CableDiag;


class Des3526Parser extends DlinkParser
{
    protected function waitToDiag($ports_list)
    {
       usleep(50000);
       return $this;
    }
}