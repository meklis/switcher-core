<?php


namespace SwitcherCore\Switcher;


class TelnetSwitcher
{
    /**
     * @var Switcher
     */
    protected  $sw = null;
    function __construct(Switcher $switcher)
    {
        $this->sw = $switcher;
    }

}