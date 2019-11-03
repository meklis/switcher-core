<?php


function onu_mac_to_oid($mac) {
    $mac = str_replace(".", "", $mac);
    $macBlock = str_split($mac);
    return
        hexdec($macBlock[0] . $macBlock[1]) . "." .
        hexdec($macBlock[2] . $macBlock[3]) . "." .
        hexdec($macBlock[4] . $macBlock[5]) . "." .
        hexdec($macBlock[5] . $macBlock[7]) . "." .
        hexdec($macBlock[6] . $macBlock[9]) . "." .
        hexdec($macBlock[0] . $macBlock[11]);

}

echo onu_mac_to_oid($argv[1]) . "\n";