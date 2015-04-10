<?php

namespace Idephix\Config;

class Config
{
    private $config = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        $name = explode('.', $name);
        
        $result = $this->config;
        
        foreach ($name as $i => $part) {
            if (!isset($result[$part])) {
                return $default;
            }
            $result = $result[$part];
        }
        
        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;

            return;
        }
        
        $name = array_reverse(explode('.', $name));

        $result = $value;
        foreach ($name as $i => $part) {
            $result = array($part => $result);
        }
        
        $this->config = array_merge_recursive($this->config, $result);
    }

    /**
     * Add trailing slash to the path if it is omitted
     *
     * @param string $name
     * @param string $default
     * @return string fixed path
     */
    public function getFixedPath($name, $default = '')
    {
        return rtrim($this->get($name, $default), '/').'/';
    }

    /**
     * @param string[] $matches
     * @return bool
     */
    protected function isSetFirstAndSecondLevel($matches)
    {
        return isset($this->config[$matches['first_level']]) &&
        isset($this->config[$matches['first_level']][$matches['second_level']]);
    }
}
