<?php
require __DIR__ . '/../vendor/autoload.php';
$rustart = getrusage();


for ($i=0; $i < 1000; $i++) {
    new \SwitcherCore\Switcher\CoreConnector(\SwitcherCore\Modules\Helper::getBuildInConfig());
}


// Script end
function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
        -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

$ru = getrusage();
echo "This process used " . rutime($ru, $rustart, "utime") .
    " ms for its computations\n";
echo "It spent " . rutime($ru, $rustart, "stime") .
    " ms in system calls\n";

