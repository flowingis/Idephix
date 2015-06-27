<?php

namespace Idephix\File;

class FunctionBasedIdxFileTest extends \PHPUnit_Framework_TestCase
{
    private $idxFile;

    protected function setUp()
    {
        $this->idxFile = tmpfile();
    }

    public function testItShouldReadTargetsFromVariable()
    {
        $idxFileContent =<<<'EOD'
<?php

$targets = array('foo' => 'bar');
EOD;

        $idxFile = $this->writeTestIdxFile($idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);

        $this->assertEquals(array('foo' => 'bar'), $file->targets());
    }

    public function testItShouldUseFunctionsAsTasks()
    {
        $idxFileContent =<<<'EOD'
<?php

function foo($bar){ echo $bar; }

EOD;

        $idxFile = $this->writeTestIdxFile($idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);

        $this->assertInternalType('array', $tasks = $file->tasks());
        $this->assertArrayHasKey('foo', $tasks);
        $this->assertInstanceOf('\Closure', $tasks['foo']);

        $task = new \ReflectionFunction($tasks['foo']);
        $this->assertEquals(1, $task->getNumberOfParameters());
    }

    public function testItShouldRemoveUnderscoreFromTaskName()
    {
        $idxFileContent =<<<'EOD'
<?php

function _echo_($bar){ echo $bar; }

EOD;

        $idxFile = $this->writeTestIdxFile($idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);

        $this->assertInternalType('array', $tasks = $file->tasks());
        $this->assertArrayHasKey('echo', $tasks);
        $this->assertInstanceOf('\Closure', $tasks['echo']);

        $task = new \ReflectionFunction($tasks['echo']);
        $this->assertEquals(1, $task->getNumberOfParameters());
    }

    public function testIsShouldResolveTasksTypeHinting()
    {
        $idxFileContent =<<<'EOD'
<?php

use Idephix\Idephix as Idx;

function foo(Idx $idx, $bar){echo 'bar';};

EOD;

        $idxFile = $this->writeTestIdxFile($idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);
        $tasks = $file->tasks();

        $task = new \ReflectionFunction($tasks['foo']);

        $this->assertEquals(2, $task->getNumberOfParameters());
        $parameters = $task->getParameters();
        $this->assertEquals('Idephix\Idephix', $parameters[0]->getClass()->getName());
        $this->assertEquals('idx', $parameters[0]->getName());
        $this->assertEquals('bar', $parameters[1]->getName());
    }

    /**
     * @param $idxFileContent
     * @return resource
     */
    private function writeTestIdxFile($idxFileContent)
    {
        fwrite($this->idxFile, $idxFileContent);
        $tmpFileData = stream_get_meta_data($this->idxFile);

        return $tmpFileData['uri'];
    }
}