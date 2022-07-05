<?php


namespace SwitcherCore\Modules\CData\FD11XX;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntReset extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $interface = $this->parseInterface($filter['interface']);
        if ($interface['type'] !== 'ONU') {
            throw new \Exception("Only ONTs allow to reboot");
        }
        if (!preg_match('/pon1\/([0-9]{1,2}):([0-9]{1,4})/', $interface['name'], $m)) {
            throw new \Exception("Error parse named interface");
        }
        $this->console->exec("olt {$m[1]}");
        $this->console->exec("onu {$m[2]}");
        $this->console->exec("default");
        return $this;
    }
}

