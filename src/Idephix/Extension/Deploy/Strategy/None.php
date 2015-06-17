<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Config\ConfigInterface;
use Idephix\IdephixInterface;

class None implements DeployStrategyInterface
{
    public function __construct(IdephixInterface $idx, ConfigInterface $target)
    {
    }

    /**
     * @inheritdoc
     */
    public function deploy()
    {
    }
}
