<?php
namespace Idephix\Config\Targets;

class Lazy implements TargetsInterface
{
    private $targets;

    public function __construct(TargetsInterface $targets)
    {
        $this->targets = $targets;
    }

    public function get($name, $default = null)
    {
        $config = $this->targets->get($name, $default);

        if ($config instanceof \Closure) {
            $config = $config();
        }

        return $config;
    }

    public function set($name, $value)
    {
        $this->targets->set($name, $value);
    }

    public function getFixedPath($name, $default = '')
    {
        return $this->targets->getFixedPath($name, $default);
    }

    public function all()
    {
        return $this->targets->all();
    }
}
