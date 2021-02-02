<?php


namespace SwitcherCore\Modules\RouterOS;



class AddressListInfo extends ExecCommand
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
        $data = [];
        $req = [];
        if(isset($params['name']) && $params['name']) {
            $req['?list'] = $params['name'];
        }
        if(isset($params['address']) && $params['address']) {
            $req['?address'] = $params['address'];
        }
        foreach ($this->execComm(' /ip/firewall/address-list/print', $req) as $d) {
            $data[] = [
                '_id' => $d['.id'],
                'name' => $d['list'],
                'address' => $d['address'],
                'created' => (\DateTime::createFromFormat('M/d/Y H:i:s', strtolower($d['creation-time'])))->format('Y-m-d H:i:s'),
                'dynamic' => $d['dynamic'] == "true" ? true : false ,
                'disabled' => $d['disabled'] == "true" ? true : false ,
            ];
        }
        $this->response = $data;
        return $this;
    }
}