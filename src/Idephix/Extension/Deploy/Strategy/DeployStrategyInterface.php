<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\IdephixInterface;
use Idephix\Config\Config;

interface DeployStrategyInterface
{
    public function __construct(IdephixInterface $idx, Config $target);

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     */
    public function deploy();
}
