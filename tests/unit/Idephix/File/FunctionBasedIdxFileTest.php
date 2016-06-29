<?php

namespace Idephix\File;

use Idephix\SSH\SshClient;

class FunctionBasedIdxFileTest extends \PHPUnit_Framework_TestCase
{
    private $idxFile;

    private $configFile;

    protected function setUp()
    {
        $this->idxFile = tmpfile();
        $this->configFile = tmpfile();
    }

    public function testItShouldReadConfigFromIdxrcFile()
    {
        $configFileContent =<<<'EOD'
<?php

use \Idephix\SSH\SshClient;

$targets = array('foo' => 'bar', 'foolazy' => function(){return 'bar';});
return \Idephix\Environment::fromArray(array('targets' => $targets, 'sshClient' => new SshClient()));

EOD;

        $configFile = $this->writeFile($this->configFile, $configFileContent);
        $idxFile = $this->writeFile($this->idxFile, '');
        $file = new FunctionBasedIdxFile($idxFile, $configFile);

        $executionContext = $file->executionContext();
        $this->assertEquals(array('foo' => 'bar', 'foolazy' => function () {return 'bar';}), $executionContext['targets']);
        $this->assertEquals(new SshClient(), $executionContext['sshClient']);
    }

    public function testItShouldUseFunctionsAsTasks()
    {
        $idxFileContent =<<<'EOD'
<?php

function foo($bar){ echo $bar; }

EOD;

        $idxFile = $this->writeFile($this->idxFile, $idxFileContent);
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

        $idxFile = $this->writeFile($this->idxFile, $idxFileContent);
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

        $idxFile = $this->writeFile($this->idxFile, $idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);
        $tasks = $file->tasks();

        $task = new \ReflectionFunction($tasks['foo']);

        $this->assertEquals(2, $task->getNumberOfParameters());
        $parameters = $task->getParameters();
        $this->assertEquals('Idephix\Idephix', $parameters[0]->getClass()->getName());
        $this->assertEquals('idx', $parameters[0]->getName());
        $this->assertEquals('bar', $parameters[1]->getName());
    }

    public function testItShouldReadLibraries()
    {
        $this->markTestIncomplete('We need to decide how the user
        should define libraries. Here are some options:
        - variables, parsed by Type (an IdxLibrary interface maybe)
        - a restricted function (i.e. idx_libraries() )
        - including an external idxFile');
    }

    /**
     * @param $file
     * @param $content
     * @return string created file uri
     */
    private function writeFile($file, $content)
    {
        fwrite($file, $content);
        $tmpFileData = stream_get_meta_data($file);

        return $tmpFileData['uri'];
    }
}
