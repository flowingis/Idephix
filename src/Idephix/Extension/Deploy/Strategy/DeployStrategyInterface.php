<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\IdephixInterface;

interface DeployStrategyInterface
{
    /**
     * @param IdephixInterface $idx
     * @param Context $currentContext
     */
    public function __construct(IdephixInterface $idx, Context $currentContext);

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     *
     * @return string|null
     */
    public function deploy();
}
