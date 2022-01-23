<?php


namespace SwitcherCore\Modules\RouterOS;



use Khill\Duration\Duration;

class BgpSessionsInfo extends ExecCommand
{

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }
    public function run($params = [])
    {
        $resp = [];
        $request = [];
        if(isset($params['name']) && $params['name']) {
            $request['?name'] = $params['name'];
        }
        if(isset($params['remote_address']) && $params['remote_address']) {
            $request['?remote-address'] = $params['remote_address'];
        }
        if(isset($params['remote_as']) && $params['remote_as']) {
            $request['?remote-as'] = $params['remote_as'];
        }
        foreach ($this->execComm('/routing/bgp/peer/print', $request) as $session) {
            $resp[] = [
                '_id' =>  isset($session['.id']) ? $session['.id'] : null,
                'name' =>  isset($session['name']) ? $session['name'] : null,
                'instance' =>  isset($session['instance']) ? $session['instance'] : null,
                'remote_address' =>  isset($session['remote-address']) ? $session['remote-address'] : null,
                'remote_as' =>  isset($session['remote-as']) ? $session['remote-as'] : null,
                'nexthop_choice' =>  isset($session['nexthop-choice']) ? $session['nexthop-choice'] : null,
                'multihop' =>  isset($session['multihop']) ? $session['multihop'] : null,
                'route_reflect' =>  isset($session['route-reflect']) ? $session['route-reflect'] : null,
                'hold_time' =>  isset($session['hold-time']) ? $session['hold-time'] : null,
                'ttl' =>  isset($session['ttl']) ? $session['ttl'] : null,
                'in_filter' =>  isset($session['in-filter']) ? $session['in-filter'] : null,
                'out_filter' =>  isset($session['out-filter']) ? $session['out-filter'] : null,
                'address_families' =>  isset($session['address-families']) ? $session['address-families'] : null,
                'remove_private_as' =>  isset($session['remove-private-as']) ? $session['remove-private-as'] == 'true' : null,
                'remote_id' =>  isset($session['remote-id']) ? $session['remote-id'] : null,
                'as_override' =>  isset($session['as-override']) ? $session['as-override'] : null,
                'local_address' =>  isset($session['local-address']) ? $session['local-address'] : null,
                'uptime' =>  isset($session['uptime']) ? $session['uptime'] : null,
                'started_at' => isset($session['uptime']) ? $this->startedAt($session['uptime']) : null,
                'prefix_count' =>  isset($session['prefix-count']) ? $session['prefix-count'] : null,
                'updates_sent' =>  isset($session['updates-sent']) ? $session['updates-sent'] : null,
                'updates_received' =>  isset($session['updates-received']) ? $session['updates-received'] : null,
                'state' =>  isset($session['state']) ? $session['state'] : null,
                'established' =>  isset($session['established']) ? $session['established'] == 'true' : null,
                'disabled' =>  isset($session['disabled']) ? $session['disabled'] == 'true' : null,
                'remote_hold_time' =>  isset($session['remote-hold-time']) ? $session['remote-hold-time'] : null,
                'used_hold_time' =>  isset($session['used-hold-time']) ? $session['used-hold-time'] : null,
                'used_keepalive_time' =>  isset($session['used-keepalive-time']) ? $session['used-keepalive-time'] : null,
                'as4_capability' =>  isset($session['as4-capability']) ? $session['as4-capability'] == 'true' : null,
                'refresh_capability' =>  isset($session['refresh-capability']) ? $session['refresh-capability'] == 'true' : null,
            ];
        }
        $this->response = $resp;
        return $this;
    }

    public function startedAt($uptime) {
        $duration = new Duration($uptime);
        return time() - $duration->toSeconds();
    }

}