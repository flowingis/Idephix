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

    public function set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;

            return;
        }

        if (preg_match('/^(?<first_level>[^.]*)\.(?<second_level>.*)$/', $name, $matches)) {
            if (isset($this->config[$matches['first_level']]) &&
                isset($this->config[$matches['first_level']][$matches['second_level']])) {

                $this->config[$matches['first_level']][$matches['second_level']] = $value;

                return;
            }
        }

        $this->config[$name] = $value;
    }

    /**
     * Add trailing slash to the path if it is omitted
     * @param string $path
     *
     * @return string fixed path
     */
    public function getFixedPath($name, $default = '')
    {
        return rtrim($this->get($name, $default), '/').'/';
    }
}