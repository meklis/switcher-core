<?php
require __DIR__ . "/../vendor/autoload.php";

$reader = new \Switcher\Config\Reader(__DIR__ . "/../configs");
$model = Switcher\Config\ModelCollector::init($reader)->getModelByDetect('D-Link DES-3028');
$oids = Switcher\Config\OidCollector::init($reader)->readEnterpriceOids($model);


$wrapper = new \SnmpWrapper\WrapperWorker("http://127.0.0.1:8080");
$walker =  (new \SnmpWrapper\Walker($wrapper))
    ->useCache(true)
    ->setIp('10.50.124.132')
    ->setCommunity('kievsnmprw');

$pooler_response = $walker->walk([
    $oids->getOidByName('dot1q.FdbPort')->getOid(),
    $oids->getOidByName('sys.Descr')->getOid(),
]);

foreach ($pooler_response as $walk) {
    foreach ($walk->getResponse() as $resp) {
        echo $resp->getOid() . " - " . $oids->getOidByRegexId($resp->getOid())->getName() . "\n";
    }
}
