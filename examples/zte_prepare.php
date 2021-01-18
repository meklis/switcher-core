<?php
$input="
Rack Shelf Slot CfgType RealType Port  HardVer SoftVer         Status
-------------------------------------------------------------------------------
1    1     1    ETGO    ETGOD    8     V1.0.0  V2.1.0          INSERVICE
1    1     2    GTGO    GTGOG    8     V1.0.0  V2.1.0          INSERVICE
1    1     3    PRAM    PRAM     3     V1.0.0  V1.01           INSERVICE
1    1     4    SMXA    SMXA     3     V1.0.0  V2.1.0          INSERVICE

 
";


print_r(parseTable($input));


