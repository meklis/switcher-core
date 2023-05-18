<?php


namespace SwitcherCore\Modules\ZTE\C300Series;



use Exception;
use Monolog\Logger;
use SwitcherCore\Config\Objects\Oid;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Modules\ZTE\ModuleAbstract;

class CardList extends ModuleAbstract
{
    /**
     * @Inject
     * @var Logger
     */
    protected $logger;

    public function run($params = [])
    {

        $cache = $this->getCache('zte_card_list', true);
        if($cache) {
            $this->response = $cache;
            return  $this;
        } else {
            $this->logger->notice("Cache by key 'card_list' not found");
        }

        $response = $this->formatResponse($this->snmp->walkNext([
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.CfgType')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.NumPorts')->getOid()),
            \SnmpWrapper\Oid::init($this->oids->getOidByName('zx.slot.FirmwareVer')->getOid()),
        ]));
        $RESP = [];
        //Validate not errors
        foreach ($response as $k=>$data) {
            if($data->error()) {
                throw new \Exception("Error call {$k} with message: {$data->error()}");
            }
        }
        foreach ($response['zx.slot.CfgType']->fetchAll() as $type) {
            $rack = (int)Helper::getIndexByOid($type->getOid(), 2);
            $shelf = (int)Helper::getIndexByOid($type->getOid(), 1);
            $slot = (int)Helper::getIndexByOid($type->getOid());
            $technology = null;
            if(preg_match('/^ET/', $type->getValue())) {
                $technology = 'epon';
            } elseif (preg_match('/^GT/', $type->getValue())) {
                $technology = 'gpon';
            }
            $RESP["{$rack}/{$shelf}/{$slot}"] = [
                'rack' => $rack,
                'shelf' => $shelf,
                'slot' => $slot,
                'cfg_type' => $type->getValue(),
                'technology' => $technology,
                'id' => (int)"10{$rack}{$shelf}{$slot}",
            ];
        }
        foreach ($response['zx.slot.NumPorts']->fetchAll() as $type) {
            $rack = Helper::getIndexByOid($type->getOid(), 2);
            $shelf = Helper::getIndexByOid($type->getOid(), 1);
            $slot = Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['num_ports'] = $type->getValue();
        }
        foreach ($response['zx.slot.FirmwareVer']->fetchAll() as $type) {
            $rack = Helper::getIndexByOid($type->getOid(), 2);
            $shelf = Helper::getIndexByOid($type->getOid(), 1);
            $slot = Helper::getIndexByOid($type->getOid());
            $RESP["{$rack}/{$shelf}/{$slot}"]['soft_ver'] = $type->getValue();
        }

        $this->response = array_values($RESP);
        $this->setCache('zte_card_list', $this->response, 300, true);
        return  $this;
    }
    public function getPretty()
    {
        return $this->response;
    }

    public function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

}