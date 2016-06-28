<?php
namespace Idephix\Config\Targets;

interface TargetsInterface
{
    public function get($name, $default = null);

    public function set($name, $value);

    public function getFixedPath($name, $default = '');
}
