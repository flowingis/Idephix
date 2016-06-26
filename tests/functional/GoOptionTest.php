<?php


namespace Idephix;


class GoOptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->idxFile = __DIR__ . "/../idxfile.php";
        $this->idxLegacyFile = __DIR__."/../legacyIdxFile.php";
        $this->idxConfig = __DIR__ . "/../idxrc.php";
        $this->idxBin = __DIR__ . "/../../bin/idx";
    }

    public function testLegacySyntaxDryRun()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxLegacyFile} fakedeploy",
            $output,
            $exitCode
        );

        $this->assertEquals(array('dry-run'), $output);
    }

    public function testLegacySyntaxRealRun()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxLegacyFile} fakedeploy --go",
            $output,
            $exitCode
        );

        $this->assertEquals(array('real-run'), $output);
    }

    public function testNewSyntaxDryRun()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxFile} fakedeploy",
            $output,
            $exitCode
        );

        $this->assertEquals(array('dry-run'), $output);
    }

    public function testNewSyntaxRealRun()
    {
        exec(
            "php " . $this->idxBin . " -f {$this->idxFile} fakedeploy --go",
            $output,
            $exitCode
        );

        $this->assertEquals(array('real-run'), $output);
    }

}