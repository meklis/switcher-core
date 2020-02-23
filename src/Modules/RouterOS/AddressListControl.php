<?php


namespace SwitcherCore\Modules\RouterOS;


class AddressListControl extends ExecCommand
{
    protected $status = false;

    function getPretty()
    {
        return $this->response;
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->response;
    }

    private function getIdsByParams($name = "", $address = "")
    {

        $ids = [];
        foreach ($this->module->address_list_info->run([
            'name' => $name,
            'address' => $address,
        ])->getPrettyFiltered() as $id) {
            $ids[] = $id['_id'];
        }
        return $ids;
    }

    public function run($params = [])
    {
        $ids = [];
        if ($params['action'] != 'add') {
            if ($params['_id']) {
                $ids[] = $params['_id'];
            }
            if ($params['name'] || $params['address']) {
                $ids = array_merge($ids, $this->getIdsByParams($params['name'], $params['address']));
            }
        }

        switch ($params['action']) {
            case 'add':
                if (!$params['name']) throw new \InvalidArgumentException("Name is required field from adding");
                if (!$params['address']) throw new \InvalidArgumentException("Address is required field from adding", 404);
                $this->add($params);
                break;
            case 'remove':
                if (!$params['_id'] && !$params['name'] && !$params['address']) throw new \InvalidArgumentException("_id or name or address is required for remove");
                if (!$ids) throw new \InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'remove');
                break;
            case 'disable':
                if (!$params['_id'] && !$params['name'] && !$params['address']) throw new \InvalidArgumentException("_id or name or address is required for disable");
                if (!$ids) throw new \InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'disable');
                break;
            case 'enable':
                if (!$params['_id'] && !$params['name'] && !$params['address']) throw new \InvalidArgumentException("_id or name or address is required for enable");
                if (!$ids) throw new \InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'enable');
                break;
            default:
                throw new \Exception("Incorrect action. Allowed add,remove,disable,enable");
        }
        return $this;
    }

    private function add($params)
    {
        $req = [
            'address' => $params['address'],
            'list' => $params['name'],
            'comment' => $params['comment'],
        ];
        if ($params['timeout']) {
            $req['timeout'] = $params['timeout'];
        }
        if ($params['comment']) {
            $req['comment'] = $params['comment'];
        }
        $result = $this->execComm("/ip/firewall/address-list/add", $req);
        $this->response = $result;
        return $this;
    }

    private function addressAction($ids, $action = 'remove')
    {
        $responses = [];
        foreach ($ids as $id) {
            $resp = $this->execComm("/ip/firewall/address-list/$action", [
                'numbers' => $id
            ]);
            $responses[$id] = $resp ? false : true ;
        }
        $this->response = $responses;
        return $this;
    }

}