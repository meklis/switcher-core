<?php


namespace SwitcherCore\Modules\Snmp\ZTE;

use SwitcherCore\Exceptions\NoSuchInstanceException;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class OntInfo extends ZteAbstractModule
{

    public function run($params = [])
    {
        $index = "";
        if($params['port']) {
            $index = "." . ($this->getIndexByPort($this->getSlotByPort($params['port']), 'slot'));
            $ont = $this->getOntByPort($params['port']);
            if($ont != -1) {
                $index .= ".{$ont}";
            }
        }
        $oidsObj = $this->oidsCollector->getOidsByRegex('^zx.ont.*');
        $oids = [];
        foreach ($oidsObj as $oid) {
            $oids[] = $oid->getOid() . "{$index}";
        }
        try {
            $this->response = $this->formatResponse($this->walker->walk($oids));
        } catch (\Exception $e) {
            if(strpos($e->getMessage(), "NoSuchInstance") !== false) {
                throw new NoSuchInstanceException($e->getMessage(), 1, $e);
            }
        }
        return $this;
    }

    private function getSlotByPort($port) {
        return explode(":", $port)[0];
    }
    private function getOntByPort($port) {
        $data = explode(":", $port);
        if(count($data) <= 1) {
            return -1;
        }
        return $data[1];
    }

    protected function format() {
        $response = [];
        /*
- {name: zx.ont.RtdDistance, oid: .1.3.6.1.4.1.3902.1012.3.11.4.1.2}
- {name: zx.ont.RtdEqd, oid: .1.3.6.1.4.1.3902.1012.3.11.4.1.1}
- {name: zx.ont.DevMgmtTypeName, oid: .1.3.6.1.4.1.3902.1012.3.28.1.1.1}
- {name: zx.ont.DevMgmtName, oid: .1.3.6.1.4.1.3902.1012.3.28.1.1.2}
- {name: zx.ont.OntVendor, oid: .1.3.6.1.4.1.3902.1012.3.50.11.2.1.1}
- {name: zx.ont.DevMgmtProvisionSn, oid: .1.3.6.1.4.1.3902.1012.3.28.1.1.5}
- {name: zx.ont.VportMode, oid: .1.3.6.1.4.1.3902.1012.3.28.1.1.10, values: { 1: gemport, 2: onu, 3: manual}}
- {name: zx.ont.AdminState, oid: .1.3.6.1.4.1.3902.1012.3.50.11.2.1.7, values: { 1: unlock, 2: lock}}
- {name: zx.ont.PhaseState, oid: .1.3.6.1.4.1.3902.1012.3.28.2.1.4, values: { 0: logging, 1: los, 2: syncMib, 3: working, 4: dyinggasp, 5: authFailed, 6: offline}}
- {name: zx.ont.LastOnlineTime, oid: .1.3.6.1.4.1.3902.1012.3.28.2.1.5}
- {name: zx.ont.LastOfflineTime, oid: .1.3.6.1.4.1.3902.1012.3.28.2.1.6}
- {name: zx.ont.LastOfflineReason , oid: .1.3.6.1.4.1.3902.1012.3.28.2.1.7, values: { 1: unknown, 2: los, 3: losi, 4: lofi, 5: sfi, 6: loai, 7: loami, 8: authFail, 9: dyingGasp, 10: deactiveSucc, 11: deactiveFail, 12: reboot, 13: shutdown }}

         */

        foreach ($this->response as $name=>$data) {
            $name = Helper::fromCamelCase(str_replace('zx.ont', '',$name));
            foreach ($data->fetchAll() as $resp) {
                $portArr = Helper::ztePonIndexDecode(Helper::getIndexByOid($resp->getOid() + $this->index_bias,1));
                $port = "{$portArr['shelf']}/{$portArr['slot']}/{$portArr['olt']}";
                $ont = Helper::getIndexByOid($resp->getOid());
                $response["{$port}:$ont"]['port'] =  "{$port}:{$ont}" ;
                $response["{$port}:$ont"]['slot'] = $port;
                $response["{$port}:$ont"]['ont'] = $ont;
                $response["{$port}:$ont"][$name] = $resp->getParsedValue();
                $response["{$port}:$ont"]['index_parsed'] = $portArr;
                $response["{$port}:$ont"]['index'] = Helper::getIndexByOid($resp->getOid(),1);

            }
        }
        return $response;
    }
    public function getPretty()
    {
        // TODO: Implement getPretty() method.
    }

    public function getPrettyFiltered($filter = [])
    {
        $formated = $this->format();
        return array_values($formated);
    }

}