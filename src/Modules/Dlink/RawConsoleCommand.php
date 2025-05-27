<?php

namespace SwitcherCore\Modules\Dlink;

class RawConsoleCommand extends \SwitcherCore\Modules\General\Switches\RawConsoleCommand {
    protected function validResponse($response) {
        if (preg_match('/Next possible completions/', $response)) return false;
        if (preg_match('/Ambiguous token/', $response)) return false;
        return true;
    }
}