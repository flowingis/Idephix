<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\IdephixInterface;
use Idephix\Config\Config;

class None implements DeployStrategyInterface
{
    public function __construct(IdephixInterface $idx, Config $target)
    {
    }

    /**
     * @inheritdoc
     */
    public function deploy()
    {
    }
}