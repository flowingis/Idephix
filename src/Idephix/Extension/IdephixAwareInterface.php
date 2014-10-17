<?php

namespace Idephix\Extension;

use Idephix\IdephixInterface;

interface IdephixAwareInterface
{
    /**
     * @param IdephixInterface $idx
     * @return void
     */
    public function setIdephix(IdephixInterface $idx);
}
