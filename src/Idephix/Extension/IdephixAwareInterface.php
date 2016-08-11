<?php

namespace Idephix\Extension;

use Idephix\Idephix;

interface IdephixAwareInterface
{
    /**
     * @param Idephix $idx
     * @return
     */
    public function setIdephix(Idephix $idx);
}
