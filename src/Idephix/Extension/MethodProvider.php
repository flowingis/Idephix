<?php

namespace Idephix\Extension;

interface MethodProvider extends Extension
{
    /** @return MethodCollection */
    public function methods();

    public function name();
}
