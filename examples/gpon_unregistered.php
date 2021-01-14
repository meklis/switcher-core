<?php
$input="
OnuIndex               OnlineStatus  OamStatus   RegMac
--------------------------------------------------------------------
epon-onu_1/1/1:1       Online       complete     c4c9.ec00.0928
epon-onu_1/1/1:2       Online       complete     c4c9.ec00.0a38
epon-onu_1/1/1:3       Online       complete     e067.b3df.22f7
epon-onu_1/1/1:4       Online       complete     c4c9.ec00.0a49
epon-onu_1/1/1:5       Online       complete     e067.b3d7.95a6
epon-onu_1/1/1:6       Online       complete     c4c9.ec00.0559
epon-onu_1/1/1:7       Online       complete     c4c9.ec00.04db
epon-onu_1/1/1:8       Online       complete     c4c9.ec00.0750
epon-onu_1/1/1:9       Online       complete     e067.b3a7.8ecc
epon-onu_1/1/1:10      Online       complete     a0c6.ec06.8305
epon-onu_1/1/1:11      Online       complete     c4c9.ec00.0892
epon-onu_1/1/1:12      Online       complete     c4c9.ec00.acf9
epon-onu_1/1/1:13      Power Off    idle         0000.0000.0000
epon-onu_1/1/1:14      Online       complete     c4c9.ec00.05de
epon-onu_1/1/1:15      Power Off    idle         0000.0000.0000
epon-onu_1/1/1:16      Online       complete     c4c9.ec00.07af
epon-onu_1/1/1:17      Online       complete     c4c9.ec00.1770
epon-onu_1/1/1:18      Online       complete     c4c9.ec00.4287
epon-onu_1/1/1:19      Online       complete     c4c9.ec00.3955
epon-onu_1/1/1:20      Online       complete     c4c9.ec00.3aa9
epon-onu_1/1/1:21      Online       complete     e0e8.e629.95b6
epon-onu_1/1/1:22      Online       complete     c4c9.ec00.3781
epon-onu_1/1/1:23      Power Off    idle         0000.0000.0000
epon-onu_1/1/1:24      Online       complete     c4c9.ec00.086b
epon-onu_1/1/1:25      Online       complete     c4c9.ec00.4995
epon-onu_1/1/1:26      Online       complete     c4c9.ec00.000f
epon-onu_1/1/1:27      Online       complete     c4c9.ec00.4a67
epon-onu_1/1/1:28      Online       complete     c4c9.ec00.0bdd
epon-onu_1/1/1:29      Online       complete     c4c9.ec00.54c2
epon-onu_1/1/1:30      Online       complete     c4c9.ec00.5d6f
epon-onu_1/1/1:31      Online       complete     1c87.7912.041a
epon-onu_1/1/1:32      Online       complete     c4c9.ec00.5d19
epon-onu_1/1/1:33      Online       complete     c4c9.ec00.376b
epon-onu_1/1/1:34      Online       complete     e067.b3d7.9b40
epon-onu_1/1/1:35      Online       complete     a0c6.ec00.595a
epon-onu_1/1/1:36      Online       complete     c4c9.ec00.08f5
epon-onu_1/1/1:37      Online       complete     c4c9.ec00.0000
epon-onu_1/1/1:38      Online       complete     c4c9.ec00.05ad
epon-onu_1/1/1:39      Online       complete     1c87.7912.c08e
epon-onu_1/1/1:40      Online       complete     e0e8.e614.c2f6
epon-onu_1/1/1:41      Online       complete     c4c9.ec00.1d00
epon-onu_1/1/1:42      Power Off    idle         0000.0000.0000
epon-onu_1/1/1:43      Online       complete     1c87.7912.bc4e
epon-onu_1/1/1:44      Online       complete     c4c9.ec00.44ad
epon-onu_1/1/1:45      Online       complete     c4c9.ec00.0449
epon-onu_1/1/1:46      Online       complete     1c87.7912.be7a
epon-onu_1/1/1:47      Online       complete     c4c9.ec00.0b7c
epon-onu_1/1/1:48      Online       complete     c4c9.ec00.05e1
epon-onu_1/1/1:49      Online       complete     1c87.7912.be50
epon-onu_1/1/1:50      Online       complete     c4c9.ec00.4e76
epon-onu_1/1/1:51      Online       complete     e067.b3aa.142f
epon-onu_1/1/1:52      Power Off    idle         0000.0000.0000
epon-onu_1/1/1:54      Online       complete     c4c9.ec00.ad24
epon-onu_1/1/1:55      Online       complete     e067.b393.b375
epon-onu_1/1/1:56      Online       complete     e067.b3df.2261
epon-onu_1/1/1:57      Online       complete     c4c9.ec00.08f0
epon-onu_1/1/1:58      Online       complete     e067.b3aa.142b
epon-onu_1/1/1:59      Online       complete     e067.b3aa.1419
epon-onu_1/1/1:60      Online       complete     e067.b393.b365
epon-onu_1/1/1:61      Online       complete     c4c9.ec00.0a7c
epon-onu_1/1/1:62      Online       complete     e067.b3aa.1411
epon-onu_1/1/1:63      Online       complete     e067.b3aa.0cb3
epon-onu_1/1/1:64      Online       complete     e067.b3aa.0c9d
";

$lines = explode("\n", $input);
$response = [];
foreach (array_splice($lines, 2) as $line) {
     if(preg_match('/^(.*)[ ]{1,}(Power Off|Online)[ ]{1,}(idle|complete)[ ]{1,}(.*)$/', $line, $matches)) {
         $response[] = [
             'interface' => trim($matches[1]),
             'online_status' => trim($matches[2]),
             'oam_status' => trim($matches[3]),
             'mac' => trim($matches[4]),
             'type' => 'epon'
         ];
     }
}

print_r($response);

