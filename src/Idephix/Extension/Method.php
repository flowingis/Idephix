<?php
namespace Idephix\Extension;

interface Method
{
    public function name();
    public function __invoke();
}
