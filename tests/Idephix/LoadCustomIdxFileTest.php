<?php
namespace Idephix;

class LoadCustomIdxFileTest extends \PHPUnit_Framework_TestCase
{
    public function testCallHelloTaskFromCustomIdxFile()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx -f " .__DIR__ . "/../Test/idxfile_test.php echo 'Output by custom idx file!'");

        $this->assertContains("Output by custom idx file!", $output);
    }

    public function testCallHelloTaskFromCustomIdxFileWithLongOption()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx --file " .__DIR__ . "/../Test/idxfile_test.php echo 'Output by custom idx file!'");

        $this->assertContains("Output by custom idx file!", $output);
    }

    public function testCallTaskThatDependsOnIdxInstance()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx --file " .__DIR__ . "/../Test/idxfile_test.php greet 'Carlo'");

        $this->assertContains("Ciao Carlo", $output);
    }

    public function testCallTaskWithParamsFromCustomIdxFile()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx -f " .__DIR__ . "/../Test/idxfile_test.php idephix:test-params non per me");

        $this->assertContains("non per me", $output);
    }

    public function testLoadDefaultIdxFile()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx --no-ansi");

        $this->assertContains("Idephix version @package_version@", $output);
    }

    public function testCallHelloTaskFromNonExistentCustomIdxFile()
    {
        $output = shell_exec("php " . __DIR__ . "/../../bin/idx -f not_existent.php hello");

        $this->assertContains("file not exist!", $output);
    }
}
