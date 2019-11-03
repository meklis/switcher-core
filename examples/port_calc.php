<?php

use SwitcherCore\Modules\Helper;

require __DIR__ . "/../vendor/autoload.php";

if(is_numeric($argv[1])) {
    $id = Helper::ztePonIndexDecode($argv[1]);
    echo "Decoded: {$id['shelf']}/{$id['slot']}/{$id['olt']}:{$id['onu']}\n";
    print_r($id);
    echo "Encoded: " . Helper::ztePonIndexEncode($id['type'], $id['shelf'], $id['slot'], $id['olt'], $id['onu']) . "\n";
}
