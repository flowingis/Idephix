<?php


namespace Idephix;

interface Dictionary extends \ArrayAccess
{
    public function get($offset, $default = null);
    public function set($key, $value);
}
