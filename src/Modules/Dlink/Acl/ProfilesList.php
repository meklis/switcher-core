<?php

namespace SwitcherCore\Modules\Dlink\Acl;

use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;

class ProfilesList extends AbstractModule
{
    protected function formate()
    {
        $response = [];
        foreach ($this->response as $oidName => $resp) {
            if ($resp->error()) {
                throw new \SNMPException("Error response for oid {$oidName} - {$resp->error()}");
            }
            $key = str_replace("acl.profile.", "", $oidName);
            foreach ($resp->fetchAll() as $prof) {
                $profileId = Helper::getIndexByOid($prof->getOid());
                $value = null;
                switch ($key) {
                    case 'type':
                        $value = $prof->getParsedValue() != $prof->getValue() ? $prof->getParsedValue() : null;
                        break;
                    case 'dst_ip_mask':
                    case 'src_ip_mask':
                        $value = join('.', array_map(
                                function ($e) {
                                    return Helper::hexToDecimal($e);
                                }, explode(":", $prof->getHexValue() )
                            )
                        );
                        break;
                    case 'src_port_mask':
                    case 'dst_port_mask':
                        $value = Helper::hexToDecimal($prof->getHexValue());
                        if($value == 0) {
                            $value = null;
                        }
                        break;
                    case 'dst_mac_addr_mask':
                    case 'src_mac_addr_mask':
                        if ($prof->getHexValue() == '00:00:00:00:00:00') {
                            $value = null;
                        } else {
                            $value = $prof->getHexValue();
                        }
                        break;
                    case 'ip_protocol':
                        $value = $prof->getValue() == 0 ? null : $prof->getParsedValue();
                        break;
                    default:
                        $value = $prof->getParsedValue();
                }
                $response[$profileId]['profile_id'] = $profileId;
                $response[$profileId][$key] = $value;
            }
        }

        //Возвращаются некоторые типы, которых по факту не существует, их нужно исключать
        $response = array_filter($response, function ($profile) {
            return $profile['type'] !== null;
        });

        //Проверяем профайлы и если dst_ip_mask_type или src_ip_mask_type == 0 - зануляем соответствующие поля
        $response = array_map(function ($profile) {
            if(!$profile['dst_ip_mask_type']) {
                $profile['dst_ip_mask_type'] = null;
                $profile['dst_ip_mask'] = null;
            }
            if(!$profile['src_ip_mask_type']) {
                $profile['src_ip_mask_type'] = null;
                $profile['src_ip_mask'] = null;
            }
            return $profile;
        }, $response);

        return array_values($response);
    }

    function getPretty()
    {
        return $this->formate();
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->formate();
    }

    public function run($filter = [])
    {
        $oids = array_map(function ($e) {
            return $e->getOid();
        }, $this->oids->getOidsByRegex('^acl.profile\..*'));
        if ($filter['profile_id']) {
            foreach ($oids as $num => $oid) {
                $oids[$num] .= ".{$filter['profile_id']}";
            }
        }
        $oidObjects = [];
        foreach ($oids as $oid) {
            $oidObjects[] = Oid::init($oid);
        }

        $this->response = $this->formatResponse($this->snmp->walk($oidObjects));
        return $this;
    }
}