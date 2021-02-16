<?php

$name = 1208024;


$shelf = floor($name / 1000000);
$slot = floor(($name - ($shelf * 1000000)) / 100000);
$port = floor(($name - (($slot * 100000) + ($shelf * 1000000))) / 1000);
$onu = floor(($name - (($port * 1000) + ($slot * 100000) + ($shelf * 1000000))));
print_r([
    $shelf,
    $slot,
    $port,
    $onu
]);



