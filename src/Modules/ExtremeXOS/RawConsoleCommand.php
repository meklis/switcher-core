<?php

namespace SwitcherCore\Modules\ExtremeXOS;

class RawConsoleCommand extends \SwitcherCore\Modules\General\Switches\RawConsoleCommand {
    protected function validResponse($response) {
        if (preg_match('/Incomplete/', $response)) return false;
        if (preg_match('/bad parameter/', $response)) return false;
        if (preg_match('/invalid/', $response)) return false;
        if (preg_match('/Unrecognized/', $response)) return false;
        return true;
    }
}