<?php


namespace SwitcherCore\Modules\BDcom;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class OntDelete extends BDcomAbstractModule
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
        $iface = $this->parseInterface($filter['interface']);
        if(!preg_match('/^.*([0-9])\/([0-9]{1,4}):([0-9]{1,3})$/', $iface['name'], $match)) {
            throw new \InvalidArgumentException("Error parse ONT interface");
        }
        $this->console->exec("config");
        $this->console->exec("interface EPON {$match[1]}/{$match[2]}");
        $this->console->exec("no epon bind-onu sequence {$match[3]}");
        $this->console->exec("exit");
        $this->console->exec("exit");

        return $this;
    }
}

