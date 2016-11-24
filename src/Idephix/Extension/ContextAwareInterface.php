<?php

namespace Idephix\Extension;

use Idephix\Context;

interface ContextAwareInterface extends Extension
{
    public function setContext(Context $ctx);
}
