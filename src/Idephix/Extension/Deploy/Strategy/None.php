<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\IdephixInterface;

class None implements DeployStrategyInterface
{
    public function __construct(IdephixInterface $idx, Context $currentContext)
    {
    }

    /**
     * @inheritdoc
     */
    public function deploy()
    {
    }
}
