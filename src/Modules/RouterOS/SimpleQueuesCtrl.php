<?php


namespace SwitcherCore\Modules\RouterOS;


use Exception;
use InvalidArgumentException;

class SimpleQueuesCtrl extends ExecCommand
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

    private function getIdsByParams($name = "", $target = "")
    {
        $ids = [];
        foreach ($this->getModule('simple_queue_info')->run([
            'name' => $name,
            'target' => $target,
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
            if ($params['name'] || $params['target']) {
                $ids = array_merge($ids, $this->getIdsByParams($params['name'], $params['target']));
            }
        }

        switch ($params['action']) {
            case 'add':
                if (!$params['name']) throw new InvalidArgumentException("Name is required field from adding");
                if (!$params['target']) throw new InvalidArgumentException("Address is required field from adding");
                $this->add($params);
                break;
            case 'remove':
                if (!$params['_id'] && !$params['name'] && !$params['target']) throw new InvalidArgumentException("_id or name or address is required for remove");
                if (!$ids) throw new InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'remove');
                break;
            case 'disable':
                if (!$params['_id'] && !$params['name'] && !$params['target']) throw new InvalidArgumentException("_id or name or address is required for disable");
                if (!$ids) throw new InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'disable');
                break;
            case 'enable':
                if (!$params['_id'] && !$params['name'] && !$params['target']) throw new InvalidArgumentException("_id or name or address is required for enable");
                if (!$ids) throw new InvalidArgumentException("Addresses not found on device", 404);
                $this->addressAction($ids, 'enable');
                break;
            default:
                throw new Exception("Incorrect action. Allowed add,remove,disable,enable");
        }
        return $this;
    }

    private function add($params)
    {
        $req = [
            'target' => $params['target'],
            'name' => $params['name'],
        ];
        if ($params['comment']) $req['comment'] = $params['comment'];
        if ($params['type']) $req['queue'] = $params['type'];
        if ($params['parent']) $req['parent'] = $params['parent'];
        if ($params['limit-at']) $req['limit-at'] = $params['limit-at'];
        if ($params['max-limit']) $req['max-limit'] = $params['max-limit'];

        $result = $this->execComm("/queue/simple/add", $req);
        $this->response = $result;
        return $this;
    }

    private function addressAction($ids, $action = 'remove')
    {
        $responses = [];
        foreach ($ids as $id) {
            $resp = $this->execComm("/queue/simple/$action", [
                'numbers' => $id
            ]);
            $responses[$id] = $resp ? false : true;
        }
        $this->response = $responses;
        return $this;
    }
}