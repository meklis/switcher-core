<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Config\Objects\Trap;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class PowerStatus extends AbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

    function getRaw()
    {
        return $this->response;
    }

    public function trap(Trap $trap, $data)
    {
         print_r($data);
    }


    function getPretty()
    {
        $rawStatus = $this->getResponseByName('system.power.status')->fetchAll()[0];
        return [
            '_pretty' =>  $rawStatus->getParsedValue(),
            'status' => $rawStatus->getParsedValue() == 'A-B-normal' ? 'Good' : 'Alert',
            '_status' => $rawStatus->getValue(),
        ];
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $oid = $this->oids->getOidByName('system.power.status');
        $oArray[] = Oid::init($oid->getOid(), false);
        $this->response = $this->formatResponse($this->snmp->get($oArray));
        return $this;
    }
}

