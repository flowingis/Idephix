<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\IdephixInterface;
use Idephix\Config\Config;

interface DeployStrategyInterface
{
    /**
     * @param IdephixInterface $idx
     * @param Config $target
     * @return void
     */
    public function __construct(IdephixInterface $idx, Config $target);

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     *
     * @return string|null
     */
    public function deploy();
}
