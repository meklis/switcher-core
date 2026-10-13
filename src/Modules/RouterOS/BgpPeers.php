<?php

namespace SwitcherCore\Modules\RouterOS;

class BgpPeers extends AbstractBgp
{
    public function run($params = [])
    {
        $this->response = array_map(function ($peer) {
            return array_filter($peer, function ($key) {
                return strpos($key, '__') !== 0;
            }, ARRAY_FILTER_USE_KEY);
        }, $this->loadPeers());
        return $this;
    }
}
