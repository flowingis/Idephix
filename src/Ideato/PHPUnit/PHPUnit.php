<?php

namespace Ideato\PHPUnit;

/**
 * Description of PHPUnit wrapper
 *
 * @author kea
 */
class PHPUnit
{
    public function runPhpUnit($params_string)
    {
        passthru('phpunit '.$params_string);
    }
}