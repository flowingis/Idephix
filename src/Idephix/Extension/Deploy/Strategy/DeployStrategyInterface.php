<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Config\Targets\TargetsInterface;
use Idephix\IdephixInterface;

interface DeployStrategyInterface
{
    /**
     * @param IdephixInterface $idx
     * @param Config $target
     * @return void
     */
    public function __construct(IdephixInterface $idx, TargetsInterface $target);

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     *
     * @return string|null
     */
    public function deploy();
}
