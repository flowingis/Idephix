<?php


namespace Idephix;

interface DictionaryAccess extends \ArrayAccess
{
    public function get($offset, $default = null);
    public function set($key, $value);
}
