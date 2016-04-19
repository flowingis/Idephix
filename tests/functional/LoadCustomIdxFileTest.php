<?php
namespace Idephix;

class LoadCustomIdxFileTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->idxFile = __DIR__ . "/../idxfile.php";
        $this->idxConfig = __DIR__ . "/../idxrc.php";
        $this->idxBin = __DIR__ . "/../../bin/idx";
    }

    public function testItShouldBeAbleToUseCustomIdxFile()
    {
        $output = shell_exec(
            "php " . $this->idxBin . " -f {$this->idxFile} -c {$this->idxConfig} echo 'Output by custom idx file!'"
        );

        $this->assertContains("Output by custom idx file!", $output);

        $output = shell_exec(
            "php " . $this->idxBin . " --file {$this->idxFile} -c {$this->idxConfig}  echo 'Output by custom idx file!'"
        );

        $this->assertContains("Output by custom idx file!", $output);
    }

    public function testAnIdxInstanceShouldBeInjectedInTasksWhenNeeded()
    {
        $output = shell_exec("php " . $this->idxBin . " --file {$this->idxFile} -c {$this->idxConfig} greet 'Carlo'");

        $this->assertContains("Ciao Carlo", $output);
    }

    public function testTasksShouldReceiveExtraParams()
    {
        $output = shell_exec(
            "php " . $this->idxBin . " -f {$this->idxFile} -c {$this->idxConfig} testParams non per me"
        );

        $this->assertContains("non per me", $output);
    }

    public function testLoadDefaultIdxFile()
    {
        $output = shell_exec("php " . $this->idxBin . " --no-ansi");

        $this->assertContains("Idephix version @package_version@", $output);
        $this->assertNotContains('aTestIdxFile', $output);
    }

    public function testCallHelloTaskFromNonExistentCustomIdxFile()
    {
        $output = shell_exec("php " . $this->idxBin . " -f not_existent.php -c not_existent.php hello");

        $this->assertContains("file not exist!", $output);
    }
}
