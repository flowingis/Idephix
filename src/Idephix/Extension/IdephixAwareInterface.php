<?php

namespace Idephix\Extension;

use Idephix\Idephix;

interface IdephixAwareInterface
{
    /**
     * @param Idephix $idx
     */
    public function setIdephix(Idephix $idx);
}
