<?php

$start = 16777472;
for($i = 1; $i <= 20; $i++) {
    echo "{$i} = ".$start . "\n";
    $start += 256;
}