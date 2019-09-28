<?php


namespace SnmpSwitcher\Switcher\Objects;


class SnmpResponse extends \SnmpWrapper\Response\SnmpResponse
{
    public $parsed_value;
    function setParsed($value) {
        $this->parsed_value = $value;
        return $this;
    }
    function getParsedValue() {
        return $this->parsed_value;
    }
    function setValue($val) {
        $this->value = $val;
        return $this;
    }
    function setOid($val) {
        $this->oid = $val;
        return $this;
    }
    function setHexValue($val) {
        $this->hex_value = $val;
        return $this;
    }
    function setType($val) {
        $this->type = $val;
        return $this;
    }
}