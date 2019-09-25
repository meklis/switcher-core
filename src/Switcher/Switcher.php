<?php

namespace Switcher;

use SnmpWrapper\Walker;
use Switcher\Config\Reader;

class Switcher
{
    protected $ip;
    protected $community;
    protected $walker;
    protected $reader;
    function __construct(Walker $walker, Reader $reader)
    {

    }

}
