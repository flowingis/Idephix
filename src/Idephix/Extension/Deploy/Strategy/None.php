<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Config\Targets\TargetsInterface;
use Idephix\IdephixInterface;

class None implements DeployStrategyInterface
{
    public function __construct(IdephixInterface $idx, TargetsInterface $target)
    {
    }

    /**
     * @inheritdoc
     */
    public function deploy()
    {
    }
}
