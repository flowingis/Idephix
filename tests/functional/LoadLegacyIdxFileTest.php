<?php

namespace Idephix;

class LoadLegacyIdxFileTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->idxFile = __DIR__."/../legacyIdxFile.php";
        $this->idxBin = __DIR__."/../../bin/idx";
    }

    public function testItShouldBeAbleToRecognizeLegacyIdxFile()
    {
        $output = shell_exec(
            "php " . $this->idxBin . " -f {$this->idxFile} echo 'Output by legacy idx file!'"
        );

        $this->assertContains("Output by legacy idx file!", $output);
    }
}