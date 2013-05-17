<?php

namespace Idephix\Extension\PHPUnit;

use Idephix\Idephix;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;

/**
 * Description of PHPUnit wrapper
 *
 * @author kea
 */
class PHPUnit implements IdephixAwareInterface
{
    private $idx;

    public function runPhpUnit($params_string)
    {
        $this->idx->local('phpunit '.$params_string);
    }

    public function setIdephix(IdephixAwareInterface $idx)
    {
        $this->idx = $idx;
    }
}