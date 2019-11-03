<?php

for ($i = 1; $i<count($argv) ; $i++) {
    echo "Num: $i\n";
    echo dechex($argv[$i]) . " ";
    echo decbin($argv[$i]);

    echo "\n";
}
echo "\n";


