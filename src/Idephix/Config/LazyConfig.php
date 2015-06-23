<?php
namespace Idephix\Config;

class LazyConfig implements ConfigInterface
{
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function get($name, $default = null)
    {
        $config = $this->config->get($name, $default);

        if(is_callable($config)){
            $config = $config();
        }

        return $config;
    }

    public function set($name, $value)
    {
        $this->config->set($name, $value);
    }

    public function getFixedPath($name, $default = '')
    {
        return $this->config->getFixedPath($name, $default);
    }
}