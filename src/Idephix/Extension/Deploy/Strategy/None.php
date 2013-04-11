<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Idephix;
use Idephix\Config\Config;

class None implements DeployStrategyInterface
{
    public function __construct(Idephix $idx, Config $target)
    {
    }

    /**
     * @inheritdoc
     */
    public function deploy()
    {
    }
}