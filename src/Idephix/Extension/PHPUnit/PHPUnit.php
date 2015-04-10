<?php

namespace Idephix\Extension\PHPUnit;

use Idephix\IdephixInterface;
use Idephix\Extension\IdephixAwareInterface;

/**
 * Description of PHPUnit wrapper
 *
 * @author kea
 */
class PHPUnit implements IdephixAwareInterface
{
    private $idx;
    private $executable;

    public function __construct($executable = 'phpunit')
    {
        $this->executable = $executable;
    }

    public function runPhpUnit($params_string)
    {
        $this->idx->local($this->executable.' '.$params_string);
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }
}
