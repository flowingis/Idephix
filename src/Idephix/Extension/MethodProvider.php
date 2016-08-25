<?php

namespace Idephix\Extension;

interface MethodProvider
{
    /** @return MethodCollection */
    public function methods();

    public function name();
}
