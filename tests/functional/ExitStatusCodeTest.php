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

    public function testExitStatusOnSuccess()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxFile} echo 'Output by legacy idx file!'",
            $output,
            $exitCode
        );

        $this->assertEquals(0, $exitCode);
    }

}
