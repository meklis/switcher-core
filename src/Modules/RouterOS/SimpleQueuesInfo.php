<?php


namespace SwitcherCore\Modules\RouterOS;


class SimpleQueuesInfo extends ExecCommand
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
        if (isset($params['_id']) && $params['_id']) {
            $req['numbers'] = $params['id'];
        }
        if (isset($params['name']) && $params['name']) {
            $req['?name'] = $params['name'];
        }
        if (isset($params['target']) && $params['target']) {
            $req['?target'] = $params['target'];
        }
        if (isset($params['type']) && $params['type']) {
            $req['?queue'] = $params['type'];
        }
        if (isset($params['parent']) && $params['parent']) {
            $req['?parent'] = $params['parent'];
        }
        foreach ($this->execComm('/queue/simple/print', $req) as $d) {
            $data[] = [
                '_id' => $d['.id'],
                'name' => $d['name'],
                'target' => $d['target'],
                'parent' => $d['parent'],
                'type' => $d['queue'],
                'limit-at' => $d['limit-at'],
                'max-limit' => $d['max-limit'],
                'disabled' => $d['disabled'] == "true",
                'dynamic' => $d['dynamic'] == "true",
                'comment' => isset($d['comment']) ? $d['comment'] : "",
            ];
        }
        $this->response = $data;
        return $this;
    }
}