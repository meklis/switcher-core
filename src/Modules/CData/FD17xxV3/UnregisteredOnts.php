<?php


namespace SwitcherCore\Modules\CData\FD17xxV3;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\CData\FD17xxV3\CDataAbstractModuleFD17xxV3;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Console\ConsoleInterface;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class UnregisteredOnts extends CDataAbstractModuleFD17xxV3
{


    /**
     * @Inject
     * @var ConsoleInterface
     */
    protected $console;

    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPretty()
    {
        return $this->response;
    }

    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        $finded = $this->console->exec("show ont autofind all");
        $countOnts = 0;
        if (preg_match('/Total: ([0-9]{1,3})/m', $finded, $matches)) {
            $countOnts = (int)$matches[1];
        }
        if ($countOnts == 0) {
            $this->response = [];
            return $this;
        }
        $fieldID = 0;
        $result = [];
        foreach (explode("\n", $finded) as $line) {
            if (preg_match('/Aging time|Total/', $line)) continue;
            if (preg_match('/Frame\/Slot/', $line)) {
                $fieldID++;
                continue;
            }
            if (preg_match('/^(.*): (.*)$/', trim($line), $matches)) {
                $result[$fieldID][Helper::fromCamelCase(trim($matches[1]))] = trim($matches[2]);
            }
        }
        $portUniqIds = [];
        $data = [];
        foreach ($result as $ont) {
            $id = 1;
            if (isset($portUniqIds[$ont['port']])) {
                $portUniqIds[$ont['port']] = $portUniqIds[$ont['port']]+1;
                $id = $portUniqIds[$ont['port']];
            } else {
                $portUniqIds[$ont['port']] = $id;
            }
            if(!preg_match('/^([A-Za-z0-9]{12}).*\((.*)\)$/', $ont['sn'], $ontSN)) {
                throw new \Exception("Parse error");
            }
            $iface = $this->parseInterface("gpon0/2/{$ont['port']}");
            $iface['type'] = "ONU";
            $iface['parent'] = $iface['id'];
            $iface['id'] += $id;
            $iface['name'] .= ":{$id}";
            $iface['_onu'] = $id;
            $iface['_snmp_id'] = null;
            $data[] = [
                '_serial_ascii' => trim($ontSN[2]),
                '_serial_hex' => strtoupper(bin2hex(substr($ontSN[1], 0, 4))) . substr($ontSN[1], 4),
                'serial' => str_replace("-", "", trim($ontSN[2])),
                '_ident' => str_replace("-", "", trim($ontSN[2])),
                'interface' => $iface,
                'password' => isset($ont['password']) ? $ont['password'] : '',
                'version' => isset($ont['ont_version']) ? $ont['ont_version'] : null,
                'equipment_id' => isset($ont['equipment_id']) ? $ont['equipment_id'] : null,
                'fw_version' => isset($ont['ont_software_version']) ? $ont['ont_software_version'] : null,
                'check_code' => null,
                'loid' => isset($ont['loid']) ? $ont['loid'] : null,
                'reg_time' => isset($ont['last_autofind_time']) ? $ont['last_autofind_time'] : null,
                'model' => null,
                'type' => null,
            ];
        }
        $this->response = $data;
        return $this;
    }

    function getGponUnregisteredFromResponses($response)
    {
        $data = [];
        if (isset($response['ont.autofind.ident'])) {
            if ($response['ont.autofind.ident']->error()) {
                return [];
            }
            foreach ($response['ont.autofind.ident']->fetchAll() as $onuId => $sn) {
                $uniqId = Helper::getIndexByOid($sn->getOid());
                $ifacePort = explode(":", $this->parseInterface($uniqId, '_snmp_id')['name'])[0];
                $iface = $this->parseInterface($ifacePort . ":" . ($onuId + 1));
                $val = $sn->getValue();
                $hexVal = strtoupper(bin2hex(substr($val, 0, 4))) . substr($val, 5);
                $data[$uniqId] = [
                    '_serial_ascii' => $sn->getValue(),
                    '_serial_hex' => $hexVal,
                    'serial' => str_replace("-", "", $sn->getValue()),
                    '_ident' => str_replace("-", "", $sn->getValue()),
                    'interface' => $iface,
                    'password' => null,
                    'version' => null,
                    'equipment_id' => null,
                    'fw_version' => null,
                    'check_code' => null,
                    'loid' => null,
                    'reg_time' => null,
                    'model' => null,
                    'type' => null,
                ];
            }
        }
        foreach ($response['ont.autofind.equipmentId']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['equipment_id'] = $this->convertHexToString($d->getHexValue());
        }
        foreach ($response['ont.autofind.password']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['password'] = $this->convertHexToString($d->getHexValue());
        }
        foreach ($response['ont.autofind.softwareVer']->fetchAll() as $d) {
            $uniqId = Helper::getIndexByOid($d->getOid());
            $data[$uniqId]['fw_version'] = $this->convertHexToString($d->getHexValue());
        }
        return $data;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return parent::getPrettyFiltered($filter, $fromCache); // TODO: Change the autogenerated stub
    }
}
