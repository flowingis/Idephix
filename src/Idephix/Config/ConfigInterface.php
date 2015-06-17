<?php
namespace Idephix\Config;

interface ConfigInterface
{
    public function get($name, $default = null);

    public function set($name, $value);
}