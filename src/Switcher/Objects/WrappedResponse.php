<?php


namespace SwitcherCore\Switcher\Objects;


use Exception;
use SnmpWrapper\Response\PoollerResponse;
use SwitcherCore\Switcher\Objects\SnmpResponse as Resp;

class WrappedResponse {
    /**
     * @var PoollerResponse
     */
    private $data;
    private $counter;

    /**
     * @return string
     */
    public function error() {
        return $this->data->error;
    }

    /**
     * @return $this
     */
    public function resetFetch() {
        $this->counter = 0;
        return $this;
    }

    /**
     * @return Resp|null
     */
    public function fetchOne() {
        $counter = $this->counter;
        $this->counter++;
        if(isset($this->data->getResponse()[$counter])) {
            return $this->data->getResponse()[$counter];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isMultiResponse() {
        if(count($this->data->getResponse()) > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return Resp[]
     */
    public function fetchAll() {
        return $this->data->getResponse();
    }

    /**
     * @return PoollerResponse
     */
    function getRaw() {
        return $this->data;
    }

    /**
     * @param PoollerResponse $data
     * @return WrappedResponse
     */
    static function init($data, $wrapValues = []) {

        return new self($data, $wrapValues);
    }

    /**
     * WrappedResponse constructor.
     * @param PoollerResponse $data
     * @param null $wrapValues
     */
    protected function __construct($data, $wrapValues = null)
    {
        if($data->error == '') {
            $wrapped = [];
            foreach ($data->getResponse() as $num=>$resp) {
                if($resp->getType() == 'NoSuchInstance' || $resp->getType() == 'NoSuchObject') {
                    throw new Exception("NoSuchInstance response from device - {$data->getIp()} for oid {$resp->getOid()}");
                }
                $wrapperValue =  (new Resp())
                    ->setOid($resp->getOid())
                    ->setHexValue($resp->getHexValue())
                    ->setType($resp->getType())
                    ->setValue($resp->getValue());
                if(isset($wrapValues[$resp->getValue()])) {
                    $wrapperValue->setParsed($wrapValues[$resp->getValue()]);
                } else {
                    $wrapperValue->setParsed($resp->getValue());
                }
                $wrapped[] = $wrapperValue;
            }
            $data->response = $wrapped;
        }
        $this->data = $data;
        $this->counter = 0;
    }
}