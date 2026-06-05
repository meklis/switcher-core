<?php


namespace SwitcherCore\Modules\GCOM;


use SwitcherCore\Modules\GCOM\GCOMAbstractModule;

class MultiRawConsoleCommand extends GCOMAbstractModule
{
    function getRaw()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [], $fromCache = false)
    {
        $data = $this->getPretty();
        return $data;
    }

    function getPretty()
    {
        return $this->response;
    }


    public function run($params = [])
    {
        return $this->multiRawConsoleCommandRun($params);
    }

}
