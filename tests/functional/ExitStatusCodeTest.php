<?php


namespace Idephix;


class ExitStatusCodeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->idxFile = __DIR__ . "/../idxfile.php";
        $this->idxLegacyFile = __DIR__."/../legacyIdxFile.php";
        $this->idxConfig = __DIR__ . "/../idxrc.php";
        $this->idxBin = __DIR__ . "/../../bin/idx";
    }

    public function testNewSyntaxSuccess()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxFile} ping",
            $output,
            $exitCode
        );

        $this->assertEquals(0, $exitCode);
    }

    public function testNewSyntaxFailure()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxFile} error",
            $output,
            $exitCode
        );

        $this->assertEquals(1, $exitCode);
    }

    public function testLegacySyntaxSuccess()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxLegacyFile} ping",
            $output,
            $exitCode
        );

        $this->assertEquals(0, $exitCode);
    }

    public function testLegacySyntaxFailure()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxLegacyFile} error",
            $output,
            $exitCode
        );

        $this->assertEquals(1, $exitCode);
    }
}
