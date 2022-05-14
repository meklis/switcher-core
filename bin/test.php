<?php

use phpseclib3\Net\SSH2;
define('NET_SSH2_LOGGING', 2);


require __DIR__ . '/../vendor/autoload.php';


$ssh = new SSH2("10.15.1.2", 22);
if (!$ssh->login('service', 'billing')) {
    exit('Login Failed');
}
$ssh->setWindowSize(512, 240);
$ssh->disableSmartMFA();
$regex = "/OLT(.*?)[>#]/";
$ssh->read($regex, SSH2::READ_REGEX);
//echo $readed;
$exec = function($command) use ($ssh, $regex) {
     $ssh->write($command . "\n");;
     $buffer = "";
     while(true) {
         $line = $ssh->read("", SSH2::READ_NEXT);
         if(!is_string($line)) break;
         $buffer .= $line;
         if(preg_match($regex, $line)) {
             break;
         }
     }
     $lines = explode("\n", $buffer);
     return join("\n", array_slice($lines, 1, -1));
};


//$ssh->flushBuffer();
echo $exec("enable");
echo $exec("config");
echo $exec("?");


