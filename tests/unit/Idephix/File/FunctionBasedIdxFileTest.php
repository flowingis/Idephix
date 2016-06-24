<?php

namespace Idephix\File;

use Idephix\SSH\SshClient;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class FunctionBasedIdxFileTest extends \PHPUnit_Framework_TestCase
{
    private $idxFile;

    private $configFile;

    protected function setUp()
    {
        $this->idxFile = tmpfile();
        $this->configFile = tmpfile();
    }

    public function testItShouldReadTargetsConfigFile()
    {
        $configFileContent =<<<'EOD'
<?php

$targets = array('foo' => 'bar', 'foolazy' => function(){return 'bar';});
EOD;

        $configFile = $this->writeFile($this->configFile, $configFileContent);
        $idxFile = $this->writeFile($this->idxFile, '');
        $file = new FunctionBasedIdxFile($idxFile, $configFile);

        $this->assertEquals(array('foo' => 'bar', 'foolazy' => function () {return 'bar';}), $file->targets());
    }

    public function testItShouldReadClientFromConfigFile()
    {
        $configFileContent =<<<'EOD'
<?php

use \Idephix\SSH\SshClient;

$client = new SshClient();
EOD;

        $idxFile = $this->writeFile($this->idxFile, '');
        $configFile = $this->writeFile($this->configFile, $configFileContent);
        $file = new FunctionBasedIdxFile($idxFile, $configFile);

        $this->assertEquals(new SshClient(), $file->sshClient());
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

    public function testItShouldParseVariablesFromTheTopLevelScope()
    {
        $this->markTestIncomplete("This will fail. It's a bit tricky to
        understand the scope of a variable from a NodeVisitor implementation
        so parsing 'by name' every \$targets variable will be used as idephix
        targets configuration. Maybe enclosing everything (even configuration stuff)
        within a function would be a better solution. The downside is that we need
        to define and document e bunch of reserved functions, to be used as special
        configuration tasks instead of idephix tasks.");
        $idxFileContent =<<<'EOD'
<?php

$targets = array('foo' => 'bar');

function myCustomTask(){
    $targets = "I'm not supposed to be used as Idx targets";
}
EOD;

        $idxFile = $this->writeFile($this->idxFile, $idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);

        $this->assertEquals(array('foo' => 'bar'), $file->targets());
    }
}
