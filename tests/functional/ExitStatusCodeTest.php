<?php


namespace Idephix;

class ExitStatusCodeTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->idxFile = __DIR__ . '/../idxfile.php';
        $this->idxLegacyFile = __DIR__.'/../legacyIdxFile.php';
        $this->idxConfig = __DIR__ . '/../idxrc.php';
        $this->idxBin = __DIR__ . '/../../bin/idx';
    }

    public function testSuccess()
    {
        exec(
            'php ' . $this->idxBin . " -f {$this->idxFile} ping",
            $output,
            $exitCode
        );

        $this->assertEquals(0, $exitCode);
    }

    public function testFailure()
    {
        exec(
            'php ' . $this->idxBin . " -f {$this->idxFile} error",
            $output,
            $exitCode
        );

        $this->assertEquals(1, $exitCode);
    }
}
