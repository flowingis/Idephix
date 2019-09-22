<?php


namespace Idephix;

class GoOptionTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->idxFile = __DIR__ . '/../idxfile.php';
        $this->idxLegacyFile = __DIR__.'/../legacyIdxFile.php';
        $this->idxConfig = __DIR__ . '/../idxrc.php';
        $this->idxBin = __DIR__ . '/../../bin/idx';
    }

    public function testDryRun()
    {
        exec(
            'php ' . $this->idxBin . " -f {$this->idxFile} fakedeploy",
            $output,
            $exitCode
        );

        $this->assertEquals(array('dry-run'), $output);
    }

    public function testRealRun()
    {
        exec(
            'php ' . $this->idxBin . " -f {$this->idxFile} fakedeploy --go",
            $output,
            $exitCode
        );

        $this->assertEquals(array('real-run'), $output);
    }
}
