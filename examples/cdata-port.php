<?php

require __DIR__ . '/../vendor/autoload.php';

class abstart extends \SwitcherCore\Modules\CData\CDataAbstractModule {
    public function run($params = [])
    {
        // TODO: Implement run() method.
    }

    public function getPretty()
    {
        // TODO: Implement getPretty() method.
    }

    public function getPrettyFiltered($filter = [])
    {
        // TODO: Implement getPrettyFiltered() method.
    }

    function pars($input) {
        echo "Input: $input\n";
        echo "Output: \n";
        print_r($this->parseInterface($input));
        echo "\n";
    }
}


$cl = new abstart();


$cl->pars(16779009);
$cl->pars(16779020);
$cl->pars('pon0/0/1:10');
$cl->pars('ge0/0/2');
$cl->pars('pon0/0/1:1/1');



