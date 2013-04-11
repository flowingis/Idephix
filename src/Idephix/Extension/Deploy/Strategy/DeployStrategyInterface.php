<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Idephix;
use Idephix\Config\Config;

interface DeployStrategyInterface
{
    public function __construct(Idephix $idx, Config $target);

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     */
    public function deploy();
}