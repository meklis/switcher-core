<?php


namespace SwitcherCore\Modules\HuaweiOLT;


use Exception;
use SnmpWrapper\Oid;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GponProfiles extends HuaweiOLTAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;

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
       $oids = [
            Oid::init($this->oids->getOidByName('gpon.profiles.line.bindNum')->getOid()),
            Oid::init($this->oids->getOidByName('gpon.profiles.srv.bindNum')->getOid()),
       ];
       $response = $this->formatResponse($this->snmp->walk($oids));
       $profiles = [
           'line' => [],
           'srv' => [],
       ];
       if(isset($response['gpon.profiles.line.bindNum'])) {
           foreach ($response['gpon.profiles.line.bindNum']->fetchAll() as $profile) {
               $blk = str_replace($this->oids->getOidByName('gpon.profiles.line.bindNum')->getOid(), "", $profile->getOid());
               $profiles['line'][] = $this->oidToString($blk);
           }
       }
       if(isset($response['gpon.profiles.srv.bindNum'])) {
           foreach ($response['gpon.profiles.srv.bindNum']->fetchAll() as $profile) {
               $blk = str_replace($this->oids->getOidByName('gpon.profiles.srv.bindNum')->getOid(), "", $profile->getOid());
               $profiles['srv'][] = $this->oidToString($blk);
           }
       }
       $this->response = $profiles;
       return $this;
    }

    function oidToString($oid) {
        $str = '';
        foreach (explode(".", $oid) as $o) {
            if(!$o) continue;
            $str .= chr((int)$o);
        }
        return $str;
    }
}

