<?php

/**
 * Source: https://local.com.ua/forum/topic/76498-zte-olt-опрос-по-snmp-уровни/?page=7
 */

echo "Incomming oid number: {$argv[1]}\n";
function gpon_index_encode($shelf="0", $slot="0", $port="0") {
    if(!($shelf >= 0 && $shelf <= 64 )) throw new \Exception("Incorrect shelf number");
    if(!($slot >= 0 && $slot <= 64 )) throw new \Exception("Incorrect slot number");
    if(!($port >= 1 && $port <= 32)) throw new \Exception("Incorrect port number");
    return (("1" << 28) + (($shelf - 1) << 24) + (($slot) << 16) + (($port) << 8));
}

function gpon_index_decode($ifIndex) {
    $shelf_no       = (( $ifIndex & bindec('00001111000000000000000000000000') ) >> 24 ) + 1;
    $slot_no        = (( $ifIndex & bindec('00000000111111110000000000000000') ) >> 16 );
    $port_no        = (( $ifIndex & bindec('00000000000000001111111100000000') ) >> 8 );
    return [
        "shelf"=>$shelf_no,
        "slot"=>$slot_no,
        "port"=>$port_no,
    ];
}

$decoded = gpon_index_decode($argv[1]);
echo "Decoded \n";
print_r($decoded);
echo "\nEncoded\n";
print_r(gpon_index_encode( $decoded['shelf'], $decoded['slot'], $decoded['port']));

echo "\n";