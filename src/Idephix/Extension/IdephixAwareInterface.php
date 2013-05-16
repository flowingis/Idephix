<?php

namespace Idephix\Extension;

use Idephix\IdephixInterface;

interface IdephixAwareInterface
{
    /**
     * @param Idephix $idx
     */
    public function setIdephix(IdephixInterface $idx);
}
