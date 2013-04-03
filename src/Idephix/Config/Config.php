<?php

namespace Idephix\Config;

class Config
{
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get($name, $default = null)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        if (preg_match('/^(?<first_level>[^.]*)\.(?<second_level>.*)$/', $name, $matches)) {
            if (isset($this->config[$matches['first_level']]) &&
                isset($this->config[$matches['first_level']][$matches['second_level']])) {
                return $this->config[$matches['first_level']][$matches['second_level']];
            }
        }

        return $default;
    }
}