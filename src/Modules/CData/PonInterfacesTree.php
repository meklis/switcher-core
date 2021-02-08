<?php


namespace SwitcherCore\Modules\CData;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;


/**
 * @package SwitcherCore\Modules\CData
 */
class PonInterfacesTree extends CDataAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null;

    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        if ($filter['as_tree'] !== 'yes') {
            return $this->response;
        }
        return $this->buildTree($this->response, null);
    }

    function buildTree(array &$elements, $parentId = null)
    {
        $branch = array();
        foreach ($elements as &$element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['id']] = $element;
                unset($element);
            }
        }
        return array_values($branch);
    }

    /**
     * @param PoollerResponse[] $response
     * @return array
     */
    private function process($response)
    {
        $return = [];
        foreach ($response as $raw) {
            $oid = $this->oids->findOidById($raw->getOid());
            $pool = WrappedResponse::init($raw, $oid->getValues());
            $name = substr($oid->getName(), 8);
            switch ($name) {
                case 'opStatus':
                    $name = 'status';
                    break;
            }
            $key = Helper::fromCamelCase($name);
            if ($pool->error()) continue;
            foreach ($pool->fetchAll() as $resp) {
                $ifaceId = Helper::getIndexByOid($resp->getOid(), 2);
                $interface = $this->parseInterface($ifaceId);
                $uni = Helper::getIndexByOid($resp->getOid());
                if ($uni) {
                    $interface['uni'] = $uni;
                }
                $val = $resp->getValue();
                if (is_numeric($val)) {
                    $val = (int)$val;
                }
                if ($val == 9223372036854775807) {
                    continue;
                }
                switch ($key) {
                    case 'status':
                    case 'admin_status':
                    case 'vlan_mode':
                        $val = $resp->getParsedValue();
                        break;
                    case 'stat_in_octets':
                    case 'stat_out_octets':
                        $val = $resp->getValueAsBytes();
                        break;
                }
                $return["{$ifaceId}{$uni}"][$key] = $val;
                $return["{$ifaceId}{$uni}"]['_interface'] = $interface;
                $return["{$ifaceId}{$uni}"]['_id'] = (int)$ifaceId;
                $return["{$ifaceId}{$uni}"]['interface'] = $interface['name'] . ($interface['onu_num'] ? ":{$interface['onu_num']}" : '') . ($interface['uni'] ? "/{$interface['uni']}" : '');
            }
        }
        return array_values($return);
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
        $interfaces = $this->getModule('pon_interfaces_list')->run()->getPrettyFiltered();
        $onts = $this->getModule('pon_onts_status')->run()->getPrettyFiltered(['meta' => 'yes']);

        foreach ($interfaces as $interface) {
            $list[] = [
                'name' => $interface['name'],
                'id' => $interface['id'],
                'parent' => null,
                'type' => $interface['type'],
                'status' => null,
            ];
        }
        foreach ($onts as $ont) {
            $list[] = [
                'name' => $ont['interface'],
                'id' => $ont['_id'],
                'parent' => $ont['_interface']['id'],
                'type' => 'ONT',
                'status' => $ont['status'],
            ];
        }
        $this->response = $list;
        return $this;
    }
}

