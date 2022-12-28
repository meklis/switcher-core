<?php


namespace SwitcherCore\Modules\BDcom\GP3600;


use Exception;
use SnmpWrapper\Oid;
use SnmpWrapper\Response\PoollerResponse;
use SnmpWrapper\Response\SnmpResponse;
use SwitcherCore\Modules\AbstractModule;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Objects\WrappedResponse;

class GponProfiles extends BDcomAbstractModule
{
    /**
     * @var WrappedResponse[]
     */
    protected $response = null ;
    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        return $this->getPretty();
    }

    function getPretty()
    {
        $response = null;
        foreach ($this->response as $name => $responses) {
            if(!preg_match('/^profile\.onu\.(.*?)\.(.*)/', $name, $m)) {
                continue;
            }
            if($responses->error()) {
                throw new \Exception("Error load profiles, oid {$name} - {$responses->error()}");
            }
            foreach ($responses->fetchAll() as $resp) {
                $id = Helper::getIndexByOid($resp->getOid());
                $response[$m[1]][$id]['id'] = (int)$id;
                $response[$m[1]][$id][$m[2]] = $resp->getParsedValue();
            }
        }

        return array_map(function ($e) {
            return array_values($e);
        }, $response);
    }


    /**
     * @param array $filter
     * @return $this|AbstractModule
     * @throws Exception
     */
    public function run($filter = [])
    {
        if($this->response) {
            return  $this;
        }
        $oids = array_map(function ($oid) {
            return Oid::init($oid->getOid());
        }, $this->oids->getOidsByRegex('^profile\.onu.*'));
        $this->response = $this->formatResponse(
            $this->snmp->walk($oids)
        );
        return $this;
    }

}

