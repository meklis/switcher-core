<?php


namespace SwitcherCore\Modules\RouterOS;


use InvalidArgumentException;

class SystemResources extends ExecCommand
{

    protected $status = false;

    function getPretty()
    {
        return [
            'cpu' => [
                'util' => $this->response['cpu-load'],
                '_cpu_count' => $this->response['cpu-count'],
                '_frequency' => isset($this->response['cpu-frequency']) ? $this->response['cpu-frequency'] : null,
            ],
            'memory' => [
                'util' => round(100-($this->response['free-memory'] / $this->response['total-memory'] * 100), 2),
                '_free' => $this->response['free-memory'],
                '_total' => $this->response['total-memory'],
            ],
            'disk' => [
                'util' => round(100-($this->response['free-hdd-space'] / $this->response['total-hdd-space'] * 100), 2),
                '_free' => $this->response['free-hdd-space'],
                '_total' => $this->response['total-hdd-space'],
            ],
            'interfaces' => null,
            'cards' => null,
        ];
    }

    function getPrettyFiltered($filter = [])
    {
        return $this->getPretty();
    }

    public function run($params = [])
    {

        $resp = $this->execComm('/system/resource/print');
        if ($resp) {
            $this->response = $resp[0];
        } else {
            throw new \Exception("Empty response");
        }
        return $this;
    }
}