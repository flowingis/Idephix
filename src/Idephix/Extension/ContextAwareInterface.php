<?php

namespace Idephix\Extension;

use Idephix\Context;

interface ContextAwareInterface
{
    public function setContext(Context $ctx);
}
