<?php

namespace Idephix\Test;

use Idephix\Extension\IdephixAwareInterface;
use Idephix\Idephix;

class LibraryMock implements IdephixAwareInterface
{
    public function __construct($tester)
    {
        $this->tester = $tester;
    }

    public function setIdephix(Idephix $idx)
    {
        $this->tester->assertTrue(true, 'Set Idephix');

        return true;
    }

    public function test($return)
    {
        return $return;
    }
}