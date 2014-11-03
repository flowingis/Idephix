<?php

namespace Idephix\Test;

use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;

class LibraryMock implements IdephixAwareInterface
{
    protected $tester;

    public function __construct($tester)
    {
        $this->tester = $tester;
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->tester->assertTrue(true, 'Set Idephix');
    }

    public function test($return)
    {
        return $return;
    }
}
