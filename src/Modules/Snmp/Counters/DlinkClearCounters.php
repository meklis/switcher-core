<?php


namespace SwitcherCore\Modules\Snmp\Counters;

use SnmpWrapper\Request\PoollerRequest;
use \SwitcherCore\Modules\AbstractModule;
use \SwitcherCore\Modules\ModuleInterface;
use \SwitcherCore\Modules\Helper;


class DlinkClearCounters extends AbstractModule
{
    protected function formate() {
       return true;
    }
    function getPretty()
    {
       return true ;
    }

    function getPrettyFiltered($filter = [])
    {
        return true;
    }

    public function walk($filter = [])
    {
        $this->response = $this->formatResponse($this->walker->set(
            $this->oidsCollector->getOidByName('dlink.DevCtrlCleanAllStatisticCounter')->getOid() ,
            PoollerRequest::TypeIntegerValue,
            $this->oidsCollector->getOidByName('dlink.DevCtrlCleanAllStatisticCounter')->getValueIdByName('active')
            )
        );
        return $this;
    }
}